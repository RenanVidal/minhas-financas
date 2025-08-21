<?php

namespace App\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculateDashboardDataAction
{
    /**
     * Calculate dashboard data for the user including current balance,
     * monthly totals, and recent transactions.
     *
     * @param User $user
     * @return array
     */
    public function execute(User $user): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        // Get all transactions for balance calculation
        $allTransactions = $user->transactions()->get();
        
        // Calculate current balance
        $currentBalance = $this->calculateBalance($allTransactions);

        // Get current month transactions
        $monthlyTransactions = $user->transactions()
            ->whereBetween('date', [$currentMonth, $currentMonthEnd])
            ->get();

        // Calculate monthly totals
        $monthlyIncome = $monthlyTransactions
            ->where('type', 'income')
            ->sum('amount');

        $monthlyExpenses = $monthlyTransactions
            ->where('type', 'expense')
            ->sum('amount');

        // Get last 5 transactions
        $recentTransactions = $user->transactions()
            ->with('category')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly summary by categories
        $categorySummary = $this->getCategorySummary($monthlyTransactions);

        return [
            'current_balance' => $currentBalance,
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'monthly_net' => $monthlyIncome - $monthlyExpenses,
            'recent_transactions' => $recentTransactions,
            'category_summary' => $categorySummary,
            'has_transactions' => $allTransactions->isNotEmpty(),
        ];
    }

    /**
     * Calculate the current balance from all transactions.
     *
     * @param Collection $transactions
     * @return float
     */
    private function calculateBalance(Collection $transactions): float
    {
        $income = $transactions->where('type', 'income')->sum('amount');
        $expenses = $transactions->where('type', 'expense')->sum('amount');
        
        return $income - $expenses;
    }

    /**
     * Get category summary for the current month.
     *
     * @param Collection $transactions
     * @return Collection
     */
    private function getCategorySummary(Collection $transactions): Collection
    {
        return $transactions
            ->groupBy('category_id')
            ->map(function ($categoryTransactions) {
                $category = $categoryTransactions->first()->category;
                $total = $categoryTransactions->sum('amount');
                $count = $categoryTransactions->count();

                return [
                    'category' => $category,
                    'total' => $total,
                    'count' => $count,
                    'type' => $category->type,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }
}