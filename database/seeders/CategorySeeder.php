<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users to create default categories for each
        $users = User::all();

        $defaultIncomeCategories = [
            ['name' => 'Salário', 'description' => 'Salário mensal', 'color' => '#28a745'],
            ['name' => 'Freelance', 'description' => 'Trabalhos freelance', 'color' => '#17a2b8'],
            ['name' => 'Investimentos', 'description' => 'Rendimentos de investimentos', 'color' => '#ffc107'],
            ['name' => 'Vendas', 'description' => 'Vendas de produtos ou serviços', 'color' => '#6f42c1'],
        ];

        $defaultExpenseCategories = [
            ['name' => 'Alimentação', 'description' => 'Gastos com comida e bebida', 'color' => '#fd7e14'],
            ['name' => 'Transporte', 'description' => 'Gastos com transporte', 'color' => '#20c997'],
            ['name' => 'Moradia', 'description' => 'Aluguel, financiamento, condomínio', 'color' => '#6610f2'],
            ['name' => 'Saúde', 'description' => 'Gastos médicos e farmácia', 'color' => '#dc3545'],
            ['name' => 'Educação', 'description' => 'Cursos, livros, material escolar', 'color' => '#0d6efd'],
            ['name' => 'Lazer', 'description' => 'Entretenimento e diversão', 'color' => '#d63384'],
            ['name' => 'Compras', 'description' => 'Compras diversas', 'color' => '#495057'],
            ['name' => 'Contas', 'description' => 'Contas fixas (luz, água, internet)', 'color' => '#6c757d'],
        ];

        foreach ($users as $user) {
            // Create income categories
            foreach ($defaultIncomeCategories as $category) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'type' => 'income',
                    'color' => $category['color'],
                ]);
            }

            // Create expense categories
            foreach ($defaultExpenseCategories as $category) {
                Category::create([
                    'user_id' => $user->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'type' => 'expense',
                    'color' => $category['color'],
                ]);
            }
        }
    }
}
