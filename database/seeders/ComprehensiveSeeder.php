<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveSeeder extends Seeder
{
    /**
     * Run comprehensive seeding with different user scenarios.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive demo data...');

        // Create users with different financial profiles
        $users = $this->createUsers();

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            $this->command->info("Creating data for {$user->name}...");
            
            // Create categories
            $categories = $this->createCategoriesForUser($user);
            
            // Create scenario-specific data
            $this->createScenarioData($user, $categories, $userData['scenario']);
        }

        $this->command->info('Comprehensive seeding completed!');
    }

    private function createUsers(): array
    {
        return [
            [
                'name' => 'Ana Oliveira',
                'email' => 'ana@demo.com',
                'password' => 'demo123',
                'scenario' => 'high_earner'
            ],
            [
                'name' => 'Carlos Mendes',
                'email' => 'carlos@demo.com',
                'password' => 'demo123',
                'scenario' => 'budget_conscious'
            ],
            [
                'name' => 'Beatriz Lima',
                'email' => 'beatriz@demo.com',
                'password' => 'demo123',
                'scenario' => 'student'
            ],
            [
                'name' => 'Roberto Silva',
                'email' => 'roberto@demo.com',
                'password' => 'demo123',
                'scenario' => 'freelancer'
            ],
            [
                'name' => 'Fernanda Costa',
                'email' => 'fernanda@demo.com',
                'password' => 'demo123',
                'scenario' => 'family_budget'
            ]
        ];
    }

    private function createCategoriesForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $categories = [
            // Income
            ['name' => 'Salário', 'type' => 'income', 'color' => '#28a745', 'desc' => 'Salário mensal'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#17a2b8', 'desc' => 'Trabalhos freelance'],
            ['name' => 'Investimentos', 'type' => 'income', 'color' => '#ffc107', 'desc' => 'Rendimentos'],
            ['name' => 'Vendas', 'type' => 'income', 'color' => '#6f42c1', 'desc' => 'Vendas diversas'],
            ['name' => 'Bolsa/Auxílio', 'type' => 'income', 'color' => '#20c997', 'desc' => 'Bolsas e auxílios'],
            
            // Expenses
            ['name' => 'Alimentação', 'type' => 'expense', 'color' => '#fd7e14', 'desc' => 'Comida e bebida'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#20c997', 'desc' => 'Transporte'],
            ['name' => 'Moradia', 'type' => 'expense', 'color' => '#6610f2', 'desc' => 'Casa e moradia'],
            ['name' => 'Saúde', 'type' => 'expense', 'color' => '#dc3545', 'desc' => 'Saúde e medicina'],
            ['name' => 'Educação', 'type' => 'expense', 'color' => '#0d6efd', 'desc' => 'Educação'],
            ['name' => 'Lazer', 'type' => 'expense', 'color' => '#d63384', 'desc' => 'Entretenimento'],
            ['name' => 'Compras', 'type' => 'expense', 'color' => '#495057', 'desc' => 'Compras diversas'],
            ['name' => 'Contas', 'type' => 'expense', 'color' => '#6c757d', 'desc' => 'Contas fixas'],
            ['name' => 'Família', 'type' => 'expense', 'color' => '#e83e8c', 'desc' => 'Gastos familiares'],
        ];

        foreach ($categories as $cat) {
            Category::create([
                'user_id' => $user->id,
                'name' => $cat['name'],
                'description' => $cat['desc'],
                'type' => $cat['type'],
                'color' => $cat['color'],
            ]);
        }

        return Category::where('user_id', $user->id)->get();
    }

    private function createScenarioData(User $user, $categories, string $scenario): void
    {
        switch ($scenario) {
            case 'high_earner':
                $this->createHighEarnerData($user, $categories);
                break;
            case 'budget_conscious':
                $this->createBudgetConsciousData($user, $categories);
                break;
            case 'student':
                $this->createStudentData($user, $categories);
                break;
            case 'freelancer':
                $this->createFreelancerData($user, $categories);
                break;
            case 'family_budget':
                $this->createFamilyBudgetData($user, $categories);
                break;
        }
    }

    private function createHighEarnerData(User $user, $categories): void
    {
        // High salary, investments, expensive lifestyle
        $this->createMonthlyTransactions($user, $categories, [
            'salary' => 12000,
            'investments' => 800,
            'food_budget' => 1500,
            'transport_budget' => 800,
            'housing_budget' => 3000,
            'leisure_budget' => 2000,
        ]);

        // Ambitious goals
        Goal::create([
            'user_id' => $user->id,
            'name' => 'Casa Própria',
            'target_amount' => 200000,
            'current_amount' => 45000,
            'deadline' => now()->addYears(2),
        ]);

        Goal::create([
            'user_id' => $user->id,
            'name' => 'Aposentadoria',
            'target_amount' => 1000000,
            'current_amount' => 85000,
            'deadline' => now()->addYears(20),
        ]);
    }

    private function createBudgetConsciousData(User $user, $categories): void
    {
        // Moderate income, careful spending
        $this->createMonthlyTransactions($user, $categories, [
            'salary' => 4500,
            'investments' => 200,
            'food_budget' => 600,
            'transport_budget' => 300,
            'housing_budget' => 1200,
            'leisure_budget' => 400,
        ]);

        Goal::create([
            'user_id' => $user->id,
            'name' => 'Reserva de Emergência',
            'target_amount' => 15000,
            'current_amount' => 8500,
            'deadline' => now()->addMonths(8),
        ]);
    }

    private function createStudentData(User $user, $categories): void
    {
        // Low income, education focus
        $this->createMonthlyTransactions($user, $categories, [
            'salary' => 0,
            'scholarship' => 800,
            'food_budget' => 300,
            'transport_budget' => 150,
            'housing_budget' => 400,
            'education_budget' => 200,
            'leisure_budget' => 100,
        ]);

        Goal::create([
            'user_id' => $user->id,
            'name' => 'Notebook para Estudos',
            'target_amount' => 2500,
            'current_amount' => 650,
            'deadline' => now()->addMonths(4),
        ]);
    }

    private function createFreelancerData(User $user, $categories): void
    {
        // Variable income, irregular patterns
        $this->createFreelancerTransactions($user, $categories);

        Goal::create([
            'user_id' => $user->id,
            'name' => 'Equipamentos de Trabalho',
            'target_amount' => 8000,
            'current_amount' => 2300,
            'deadline' => now()->addMonths(6),
        ]);
    }

    private function createFamilyBudgetData(User $user, $categories): void
    {
        // Family expenses, children costs
        $this->createMonthlyTransactions($user, $categories, [
            'salary' => 6500,
            'food_budget' => 1200,
            'transport_budget' => 500,
            'housing_budget' => 1800,
            'family_budget' => 800,
            'education_budget' => 600,
            'health_budget' => 400,
            'leisure_budget' => 600,
        ]);

        Goal::create([
            'user_id' => $user->id,
            'name' => 'Educação dos Filhos',
            'target_amount' => 50000,
            'current_amount' => 12000,
            'deadline' => now()->addYears(5),
        ]);
    }

    private function createMonthlyTransactions(User $user, $categories, array $budget): void
    {
        for ($month = 0; $month < 6; $month++) {
            $date = now()->subMonths($month);
            
            // Income
            if (isset($budget['salary']) && $budget['salary'] > 0) {
                $this->createTransaction($user, $categories, 'Salário', 'income', 'Salário mensal', $budget['salary'], $date->copy()->day(5));
            }
            
            if (isset($budget['scholarship']) && $budget['scholarship'] > 0) {
                $this->createTransaction($user, $categories, 'Bolsa/Auxílio', 'income', 'Bolsa de estudos', $budget['scholarship'], $date->copy()->day(10));
            }
            
            if (isset($budget['investments']) && $budget['investments'] > 0) {
                $this->createTransaction($user, $categories, 'Investimentos', 'income', 'Rendimento mensal', $budget['investments'], $date->copy()->day(rand(1, 28)));
            }

            // Expenses
            $this->createRandomExpenses($user, $categories, $budget, $date);
        }
    }

    private function createFreelancerTransactions(User $user, $categories): void
    {
        for ($month = 0; $month < 6; $month++) {
            $date = now()->subMonths($month);
            
            // Variable freelance income
            $projectCount = rand(1, 4);
            for ($i = 0; $i < $projectCount; $i++) {
                $amount = rand(800, 3500);
                $this->createTransaction($user, $categories, 'Freelance', 'income', "Projeto #" . ($i + 1), $amount, $date->copy()->day(rand(1, 28)));
            }
            
            // Variable expenses
            $this->createRandomExpenses($user, $categories, [
                'food_budget' => rand(400, 800),
                'transport_budget' => rand(200, 400),
                'housing_budget' => 1200,
                'leisure_budget' => rand(200, 600),
            ], $date);
        }
    }

    private function createRandomExpenses(User $user, $categories, array $budget, Carbon $date): void
    {
        $expenses = [
            'food_budget' => ['Alimentação', ['Supermercado', 'Restaurante', 'Lanche', 'Delivery']],
            'transport_budget' => ['Transporte', ['Gasolina', 'Uber', 'Ônibus', 'Estacionamento']],
            'housing_budget' => ['Moradia', ['Aluguel', 'Condomínio', 'IPTU']],
            'family_budget' => ['Família', ['Escola', 'Roupas infantis', 'Brinquedos', 'Médico pediatra']],
            'education_budget' => ['Educação', ['Curso online', 'Livros', 'Material escolar']],
            'health_budget' => ['Saúde', ['Farmácia', 'Consulta', 'Exames']],
            'leisure_budget' => ['Lazer', ['Cinema', 'Netflix', 'Restaurante', 'Viagem']],
        ];

        foreach ($expenses as $budgetKey => $expenseData) {
            if (!isset($budget[$budgetKey]) || $budget[$budgetKey] <= 0) continue;
            
            [$categoryName, $descriptions] = $expenseData;
            $totalBudget = $budget[$budgetKey];
            $transactionCount = rand(2, 6);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $amount = rand($totalBudget * 0.1, $totalBudget * 0.4);
                $description = $descriptions[array_rand($descriptions)];
                
                $this->createTransaction($user, $categories, $categoryName, 'expense', $description, $amount, $date->copy()->day(rand(1, 28)));
            }
        }
    }

    private function createTransaction(User $user, $categories, string $categoryName, string $type, string $description, float $amount, Carbon $date): void
    {
        $category = $categories->where('name', $categoryName)->where('type', $type)->first();
        
        if (!$category) return;

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'description' => $description,
            'amount' => $amount,
            'type' => $type,
            'date' => $date,
        ]);
    }
}