<?php

namespace App\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GenerateFinancialChartAction
{
    /**
     * Generate financial chart data for the specified number of months.
     * Returns data formatted for Chart.js consumption.
     *
     * @param User $user
     * @param int $months
     * @return array
     */
    public function execute(User $user, int $months = 6): array
    {
        $chartData = $this->generateMonthlyData($user, $months);
        
        return [
            'labels' => $chartData->pluck('label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Receitas',
                    'data' => $chartData->pluck('income')->toArray(),
                    'backgroundColor' => 'rgba(40, 167, 69, 0.2)',
                    'borderColor' => 'rgba(40, 167, 69, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Despesas',
                    'data' => $chartData->pluck('expenses')->toArray(),
                    'backgroundColor' => 'rgba(220, 53, 69, 0.2)',
                    'borderColor' => 'rgba(220, 53, 69, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ],
                [
                    'label' => 'Saldo Acumulado',
                    'data' => $chartData->pluck('balance')->toArray(),
                    'backgroundColor' => 'rgba(0, 123, 255, 0.2)',
                    'borderColor' => 'rgba(0, 123, 255, 1)',
                    'borderWidth' => 2,
                    'fill' => false,
                ]
            ]
        ];
    }

    /**
     * Generate monthly data for the chart.
     *
     * @param User $user
     * @param int $months
     * @return Collection
     */
    private function generateMonthlyData(User $user, int $months): Collection
    {
        $data = collect();
        $runningBalance = 0;

        // Start from the oldest month and work forward
        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            // Get transactions for this month
            $monthlyTransactions = $user->transactions()
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();

            $monthlyIncome = $monthlyTransactions
                ->where('type', 'income')
                ->sum('amount');

            $monthlyExpenses = $monthlyTransactions
                ->where('type', 'expense')
                ->sum('amount');

            // Calculate running balance
            $runningBalance += ($monthlyIncome - $monthlyExpenses);

            $data->push([
                'label' => $monthStart->format('M/Y'),
                'month' => $monthStart->format('Y-m'),
                'income' => (float) $monthlyIncome,
                'expenses' => (float) $monthlyExpenses,
                'net' => (float) ($monthlyIncome - $monthlyExpenses),
                'balance' => (float) $runningBalance,
            ]);
        }

        return $data;
    }

    /**
     * Generate simplified chart data with just balance evolution.
     *
     * @param User $user
     * @param int $months
     * @return array
     */
    public function executeBalanceOnly(User $user, int $months = 6): array
    {
        $chartData = $this->generateMonthlyData($user, $months);
        
        return [
            'labels' => $chartData->pluck('label')->toArray(),
            'datasets' => [
                [
                    'label' => 'Evolução do Saldo',
                    'data' => $chartData->pluck('balance')->toArray(),
                    'backgroundColor' => 'rgba(0, 123, 255, 0.2)',
                    'borderColor' => 'rgba(0, 123, 255, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ]
            ]
        ];
    }

    /**
     * Get raw monthly data without Chart.js formatting.
     *
     * @param User $user
     * @param int $months
     * @return Collection
     */
    public function getRawData(User $user, int $months = 6): Collection
    {
        return $this->generateMonthlyData($user, $months);
    }
}