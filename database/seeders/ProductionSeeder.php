<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     * Only creates essential default categories for new users.
     */
    public function run(): void
    {
        // Create default admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador do Sistema',
                'password' => Hash::make('admin@2024!'),
                'email_verified_at' => now(),
            ]
        );

        // Create default categories for the admin user
        $this->createDefaultCategories($admin);
    }

    private function createDefaultCategories(User $user): void
    {
        $defaultCategories = [
            // Income categories
            [
                'name' => 'Salário',
                'description' => 'Salário mensal',
                'type' => 'income',
                'color' => '#28a745'
            ],
            [
                'name' => 'Freelance',
                'description' => 'Trabalhos freelance e projetos',
                'type' => 'income',
                'color' => '#17a2b8'
            ],
            [
                'name' => 'Investimentos',
                'description' => 'Rendimentos de investimentos',
                'type' => 'income',
                'color' => '#ffc107'
            ],
            [
                'name' => 'Vendas',
                'description' => 'Vendas de produtos ou serviços',
                'type' => 'income',
                'color' => '#6f42c1'
            ],
            [
                'name' => 'Outros Rendimentos',
                'description' => 'Outras fontes de renda',
                'type' => 'income',
                'color' => '#20c997'
            ],

            // Expense categories
            [
                'name' => 'Alimentação',
                'description' => 'Gastos com comida e bebida',
                'type' => 'expense',
                'color' => '#fd7e14'
            ],
            [
                'name' => 'Transporte',
                'description' => 'Gastos com transporte público, combustível, manutenção',
                'type' => 'expense',
                'color' => '#20c997'
            ],
            [
                'name' => 'Moradia',
                'description' => 'Aluguel, financiamento, condomínio, IPTU',
                'type' => 'expense',
                'color' => '#6610f2'
            ],
            [
                'name' => 'Saúde',
                'description' => 'Gastos médicos, farmácia, plano de saúde',
                'type' => 'expense',
                'color' => '#dc3545'
            ],
            [
                'name' => 'Educação',
                'description' => 'Cursos, livros, material escolar',
                'type' => 'expense',
                'color' => '#0d6efd'
            ],
            [
                'name' => 'Lazer',
                'description' => 'Entretenimento, cinema, restaurantes',
                'type' => 'expense',
                'color' => '#d63384'
            ],
            [
                'name' => 'Compras',
                'description' => 'Roupas, eletrônicos, casa',
                'type' => 'expense',
                'color' => '#495057'
            ],
            [
                'name' => 'Contas Fixas',
                'description' => 'Luz, água, internet, telefone',
                'type' => 'expense',
                'color' => '#6c757d'
            ],
            [
                'name' => 'Seguros',
                'description' => 'Seguro do carro, casa, vida',
                'type' => 'expense',
                'color' => '#343a40'
            ],
            [
                'name' => 'Impostos',
                'description' => 'IR, IPVA, taxas governamentais',
                'type' => 'expense',
                'color' => '#e83e8c'
            ],
        ];

        foreach ($defaultCategories as $categoryData) {
            Category::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $categoryData['name'],
                    'type' => $categoryData['type']
                ],
                $categoryData
            );
        }
    }
}