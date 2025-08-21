<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoUser = User::where('email', 'demo@financeiro.com')->first();
        
        if (!$demoUser) {
            return;
        }

        $incomeCategories = Category::where('user_id', $demoUser->id)
            ->where('type', 'income')
            ->get();
            
        $expenseCategories = Category::where('user_id', $demoUser->id)
            ->where('type', 'expense')
            ->get();

        // Generate transactions for the last 6 months
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $this->createMonthlyTransactions($demoUser, $incomeCategories, $expenseCategories, $startDate, $endDate);
    }    
private function createMonthlyTransactions($user, $incomeCategories, $expenseCategories, $startDate, $endDate)
    {
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $this->createIncomeTransactions($user, $incomeCategories, $currentDate);
            $this->createExpenseTransactions($user, $expenseCategories, $currentDate);
            
            $currentDate->addMonth();
        }
    }

    private function createIncomeTransactions($user, $categories, $month)
    {
        $salaryCategory = $categories->where('name', 'Salário')->first();
        $freelanceCategory = $categories->where('name', 'Freelance')->first();
        $investmentCategory = $categories->where('name', 'Investimentos')->first();

        // Monthly salary
        if ($salaryCategory) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $salaryCategory->id,
                'description' => 'Salário mensal',
                'amount' => rand(4500, 6500),
                'type' => 'income',
                'date' => $month->copy()->day(5),
            ]);
        }

        // Occasional freelance work
        if ($freelanceCategory && rand(1, 3) === 1) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $freelanceCategory->id,
                'description' => 'Projeto freelance',
                'amount' => rand(800, 2500),
                'type' => 'income',
                'date' => $month->copy()->day(rand(10, 25)),
            ]);
        }

        // Investment returns
        if ($investmentCategory && rand(1, 2) === 1) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $investmentCategory->id,
                'description' => 'Rendimento de investimentos',
                'amount' => rand(150, 500),
                'type' => 'income',
                'date' => $month->copy()->day(rand(1, 28)),
            ]);
        }
    }

    private function createExpenseTransactions($user, $categories, $month)
    {
        $expenseData = [
            'Alimentação' => [
                ['desc' => 'Supermercado', 'min' => 200, 'max' => 400, 'freq' => 4],
                ['desc' => 'Restaurante', 'min' => 30, 'max' => 80, 'freq' => 3],
                ['desc' => 'Lanche', 'min' => 15, 'max' => 35, 'freq' => 8],
            ],
            'Transporte' => [
                ['desc' => 'Gasolina', 'min' => 150, 'max' => 250, 'freq' => 2],
                ['desc' => 'Uber', 'min' => 20, 'max' => 45, 'freq' => 5],
                ['desc' => 'Estacionamento', 'min' => 5, 'max' => 15, 'freq' => 10],
            ],
            'Moradia' => [
                ['desc' => 'Aluguel', 'min' => 1200, 'max' => 1200, 'freq' => 1],
                ['desc' => 'Condomínio', 'min' => 300, 'max' => 300, 'freq' => 1],
            ],
            'Contas' => [
                ['desc' => 'Energia elétrica', 'min' => 120, 'max' => 180, 'freq' => 1],
                ['desc' => 'Internet', 'min' => 80, 'max' => 80, 'freq' => 1],
                ['desc' => 'Água', 'min' => 45, 'max' => 70, 'freq' => 1],
            ],
            'Saúde' => [
                ['desc' => 'Farmácia', 'min' => 25, 'max' => 80, 'freq' => 2],
                ['desc' => 'Consulta médica', 'min' => 150, 'max' => 300, 'freq' => 0.3],
            ],
            'Lazer' => [
                ['desc' => 'Cinema', 'min' => 25, 'max' => 40, 'freq' => 2],
                ['desc' => 'Netflix', 'min' => 30, 'max' => 30, 'freq' => 1],
                ['desc' => 'Livros', 'min' => 40, 'max' => 80, 'freq' => 0.5],
            ],
        ];

        foreach ($expenseData as $categoryName => $transactions) {
            $category = $categories->where('name', $categoryName)->first();
            
            if (!$category) continue;

            foreach ($transactions as $transactionData) {
                $frequency = $transactionData['freq'];
                $count = $frequency < 1 ? (rand(1, 100) <= ($frequency * 100) ? 1 : 0) : round($frequency);
                
                for ($i = 0; $i < $count; $i++) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'description' => $transactionData['desc'],
                        'amount' => rand($transactionData['min'], $transactionData['max']),
                        'type' => 'expense',
                        'date' => $month->copy()->day(rand(1, 28)),
                    ]);
                }
            }
        }
    }
}