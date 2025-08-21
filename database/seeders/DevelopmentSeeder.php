<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentSeeder extends Seeder
{
    /**
     * Run the database seeds for development environment.
     */
    public function run(): void
    {
        // Create multiple test users with different scenarios
        $users = [
            [
                'name' => 'João Silva',
                'email' => 'joao@teste.com',
                'password' => Hash::make('123456'),
                'scenario' => 'new_user', // User with minimal data
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@teste.com',
                'password' => Hash::make('123456'),
                'scenario' => 'active_user', // User with lots of data
            ],
            [
                'name' => 'Pedro Costa',
                'email' => 'pedro@teste.com',
                'password' => Hash::make('123456'),
                'scenario' => 'moderate_user', // User with moderate data
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $userData['password'],
                    'email_verified_at' => now(),
                ]
            );

            $this->createUserData($user, $userData['scenario']);
        }
    }

    private function createUserData(User $user, string $scenario): void
    {
        // Create categories for all users
        $this->createCategoriesForUser($user);

        switch ($scenario) {
            case 'new_user':
                // Only categories, no transactions or goals
                break;
                
            case 'moderate_user':
                $this->createModerateData($user);
                break;
                
            case 'active_user':
                $this->createActiveUserData($user);
                break;
        }
    }

    private function createCategoriesForUser(User $user): void
    {
        $categories = [
            // Income categories
            ['name' => 'Salário', 'type' => 'income', 'color' => '#28a745'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#17a2b8'],
            ['name' => 'Vendas', 'type' => 'income', 'color' => '#6f42c1'],
            
            // Expense categories
            ['name' => 'Alimentação', 'type' => 'expense', 'color' => '#fd7e14'],
            ['name' => 'Transporte', 'type' => 'expense', 'color' => '#20c997'],
            ['name' => 'Moradia', 'type' => 'expense', 'color' => '#6610f2'],
            ['name' => 'Lazer', 'type' => 'expense', 'color' => '#d63384'],
            ['name' => 'Saúde', 'type' => 'expense', 'color' => '#dc3545'],
        ];

        foreach ($categories as $categoryData) {
            Category::create([
                'user_id' => $user->id,
                'name' => $categoryData['name'],
                'description' => "Categoria {$categoryData['name']}",
                'type' => $categoryData['type'],
                'color' => $categoryData['color'],
            ]);
        }
    }

    private function createModerateData(User $user): void
    {
        $categories = Category::where('user_id', $user->id)->get();
        
        // Create some transactions for current month
        $salaryCategory = $categories->where('name', 'Salário')->first();
        $foodCategory = $categories->where('name', 'Alimentação')->first();
        $transportCategory = $categories->where('name', 'Transporte')->first();

        if ($salaryCategory) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $salaryCategory->id,
                'description' => 'Salário do mês',
                'amount' => 3500.00,
                'type' => 'income',
                'date' => now()->startOfMonth()->addDays(4),
            ]);
        }

        if ($foodCategory) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $foodCategory->id,
                'description' => 'Supermercado',
                'amount' => 250.00,
                'type' => 'expense',
                'date' => now()->subDays(3),
            ]);
        }

        if ($transportCategory) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $transportCategory->id,
                'description' => 'Gasolina',
                'amount' => 120.00,
                'type' => 'expense',
                'date' => now()->subDays(1),
            ]);
        }

        // Create one goal
        Goal::create([
            'user_id' => $user->id,
            'name' => 'Reserva de Emergência',
            'target_amount' => 5000.00,
            'current_amount' => 1200.00,
            'deadline' => now()->addMonths(6),
            'category_id' => null,
            'status' => 'active',
        ]);
    }

    private function createActiveUserData(User $user): void
    {
        // Use factories to create lots of data
        $categories = Category::where('user_id', $user->id)->get();
        
        // Create 50 transactions over the last 3 months
        Transaction::factory(50)->create([
            'user_id' => $user->id,
            'category_id' => fn() => $categories->random()->id,
        ]);

        // Create 3 goals
        Goal::factory(3)->create([
            'user_id' => $user->id,
            'category_id' => fn() => $categories->random()->id,
        ]);
    }
}