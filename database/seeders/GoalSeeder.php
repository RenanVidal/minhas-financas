<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GoalSeeder extends Seeder
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

        $categories = Category::where('user_id', $demoUser->id)->get();
        
        $goals = [
            [
                'name' => 'Reserva de Emergência',
                'target_amount' => 10000.00,
                'current_amount' => 3500.00,
                'deadline' => Carbon::now()->addMonths(8),
                'category_id' => $categories->where('name', 'Investimentos')->first()?->id,
            ],
            [
                'name' => 'Viagem de Férias',
                'target_amount' => 5000.00,
                'current_amount' => 1200.00,
                'deadline' => Carbon::now()->addMonths(6),
                'category_id' => $categories->where('name', 'Lazer')->first()?->id,
            ],
            [
                'name' => 'Novo Notebook',
                'target_amount' => 3500.00,
                'current_amount' => 2800.00,
                'deadline' => Carbon::now()->addMonths(2),
                'category_id' => $categories->where('name', 'Compras')->first()?->id,
            ],
            [
                'name' => 'Curso de Especialização',
                'target_amount' => 2000.00,
                'current_amount' => 800.00,
                'deadline' => Carbon::now()->addMonths(4),
                'category_id' => $categories->where('name', 'Educação')->first()?->id,
            ],
            [
                'name' => 'Entrada do Carro',
                'target_amount' => 15000.00,
                'current_amount' => 4500.00,
                'deadline' => Carbon::now()->addYear(),
                'category_id' => $categories->where('name', 'Transporte')->first()?->id,
            ],
        ];

        foreach ($goals as $goalData) {
            Goal::create([
                'user_id' => $demoUser->id,
                'name' => $goalData['name'],
                'target_amount' => $goalData['target_amount'],
                'current_amount' => $goalData['current_amount'],
                'deadline' => $goalData['deadline'],
                'category_id' => $goalData['category_id'],
                'status' => 'active',
            ]);
        }
    }
}