<?php

namespace App\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GenerateFinancialReportAction
{
    /**
     * Generate financial report with applied filters.
     *
     * @param User $user
     * @param array $filters
     * @return array
     */
    public function execute(User $user, array $filters = []): array
    {
        // Build query with filters
        $query = $user->transactions()->with(['category']);

        // Apply date filters
        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', Carbon::parse($filters['start_date']));
        }

        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', Carbon::parse($filters['end_date']));
        }

        // Apply category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Apply transaction type filter
        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $query->where('type', $filters['type']);
        }

        // Get transactions ordered by date (most recent first)
        $transactions = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totals = $this->calculateTotals($transactions);

        // Get totals by category
        $categoryTotals = $this->getTotalsByCategory($transactions);

        // Get totals by type
        $typeTotals = $this->getTotalsByType($transactions);

        return [
            'transactions' => $transactions,
            'totals' => $totals,
            'category_totals' => $categoryTotals,
            'type_totals' => $typeTotals,
            'filters' => $filters,
            'period' => $this->getPeriodDescription($filters),
            'has_data' => $transactions->isNotEmpty(),
        ];
    }

    /**
     * Calculate overall totals from transactions.
     *
     * @param Collection $transactions
     * @return array
     */
    private function calculateTotals(Collection $transactions): array
    {
        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        $net = $income - $expenses;

        return [
            'income' => $income,
            'expenses' => $expenses,
            'net' => $net,
            'count' => $transactions->count(),
        ];
    }

    /**
     * Get totals grouped by category.
     *
     * @param Collection $transactions
     * @return Collection
     */
    private function getTotalsByCategory(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $category = $categoryTransactions->first()->category;
                $income = $categoryTransactions->where('type', 'income')->sum('amount');
                $expenses = $categoryTransactions->where('type', 'expense')->sum('amount');
                $total = $categoryTransactions->sum('amount');
                $count = $categoryTransactions->count();

                return [
                    'category' => $category,
                    'income' => $income,
                    'expenses' => $expenses,
                    'total' => $total,
                    'net' => $income - $expenses,
                    'count' => $count,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Get totals grouped by transaction type.
     *
     * @param Collection $transactions
     * @return array
     */
    private function getTotalsByType(Collection $transactions): array
    {
        $incomeTransactions = $transactions->where('type', 'income');
        $expenseTransactions = $transactions->where('type', 'expense');

        return [
            'income' => [
                'total' => $incomeTransactions->sum('amount'),
                'count' => $incomeTransactions->count(),
                'average' => $incomeTransactions->count() > 0 ? $incomeTransactions->avg('amount') : 0,
            ],
            'expense' => [
                'total' => $expenseTransactions->sum('amount'),
                'count' => $expenseTransactions->count(),
                'average' => $expenseTransactions->count() > 0 ? $expenseTransactions->avg('amount') : 0,
            ],
        ];
    }

    /**
     * Get a human-readable description of the filtered period.
     *
     * @param array $filters
     * @return string
     */
    private function getPeriodDescription(array $filters): string
    {
        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date']) : null;
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date']) : null;

        if ($startDate && $endDate) {
            return $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
        } elseif ($startDate) {
            return 'A partir de ' . $startDate->format('d/m/Y');
        } elseif ($endDate) {
            return 'Até ' . $endDate->format('d/m/Y');
        }

        return 'Todos os períodos';
    }
}