<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $incomeCategories = [
            'Salário', 'Freelance', 'Investimentos', 'Vendas', 'Bonificação', 'Aluguel Recebido'
        ];
        
        $expenseCategories = [
            'Alimentação', 'Transporte', 'Moradia', 'Saúde', 'Educação', 'Lazer', 'Compras', 'Contas'
        ];
        
        $type = $this->faker->randomElement(['income', 'expense']);
        $categories = $type === 'income' ? $incomeCategories : $expenseCategories;
        
        return [
            'name' => $this->faker->randomElement($categories),
            'description' => $this->faker->optional()->sentence(),
            'type' => $type,
            'color' => $this->faker->hexColor(),
        ];
    }

    /**
     * Indicate that the category is for income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'name' => $this->faker->randomElement([
                'Salário', 'Freelance', 'Investimentos', 'Vendas', 'Bonificação', 'Aluguel Recebido'
            ]),
        ]);
    }

    /**
     * Indicate that the category is for expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'name' => $this->faker->randomElement([
                'Alimentação', 'Transporte', 'Moradia', 'Saúde', 'Educação', 'Lazer', 'Compras', 'Contas'
            ]),
        ]);
    }
}
