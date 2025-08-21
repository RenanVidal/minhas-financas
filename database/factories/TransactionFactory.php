<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $incomeDescriptions = [
            'Salário mensal', 'Freelance projeto X', 'Dividendos investimentos', 
            'Venda produto', 'Bonificação', 'Aluguel recebido'
        ];
        
        $expenseDescriptions = [
            'Supermercado', 'Combustível', 'Almoço', 'Uber', 'Farmácia', 
            'Cinema', 'Conta de luz', 'Internet', 'Compras online', 'Jantar'
        ];
        
        $type = $this->faker->randomElement(['income', 'expense']);
        $descriptions = $type === 'income' ? $incomeDescriptions : $expenseDescriptions;
        
        return [
            'description' => $this->faker->randomElement($descriptions),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $type,
            'date' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }

    /**
     * Indicate that the transaction is income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'description' => $this->faker->randomElement([
                'Salário mensal', 'Freelance projeto X', 'Dividendos investimentos', 
                'Venda produto', 'Bonificação', 'Aluguel recebido'
            ]),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
        ]);
    }

    /**
     * Indicate that the transaction is expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'description' => $this->faker->randomElement([
                'Supermercado', 'Combustível', 'Almoço', 'Uber', 'Farmácia', 
                'Cinema', 'Conta de luz', 'Internet', 'Compras online', 'Jantar'
            ]),
            'amount' => $this->faker->randomFloat(2, 5, 500),
        ]);
    }
}
