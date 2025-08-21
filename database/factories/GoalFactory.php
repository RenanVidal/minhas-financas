<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $goalNames = [
            'Reserva de Emergência',
            'Viagem de Férias',
            'Comprar Carro',
            'Casa Própria',
            'Curso de Especialização',
            'Equipamento de Trabalho',
            'Investimento em Ações',
            'Reforma da Casa',
            'Casamento',
            'Aposentadoria'
        ];
        
        $targetAmount = $this->faker->randomFloat(2, 1000, 50000);
        $currentAmount = $this->faker->randomFloat(2, 0, $targetAmount * 0.8);
        
        return [
            'name' => $this->faker->randomElement($goalNames),
            'target_amount' => $targetAmount,
            'current_amount' => $currentAmount,
            'deadline' => $this->faker->dateTimeBetween('now', '+2 years')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
        ];
    }

    /**
     * Indicate that the goal is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the goal is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'current_amount' => $attributes['target_amount'] ?? $this->faker->randomFloat(2, 1000, 50000),
            ];
        });
    }

    /**
     * Indicate that the goal is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the goal is expiring soon.
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => $this->faker->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'status' => 'active',
        ]);
    }
}
