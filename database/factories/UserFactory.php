<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'telephone' => fake()->phoneNumber(),
            'cin' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'taille' => fake()->randomFloat(2, 1.50, 2.10),
            'poids' => [
                [
                    'date' => fake()->date(),
                    'valeur' => fake()->randomFloat(1, 50, 120)
                ]
            ],
            'objectif' => fake()->randomElement([
                'prise de masse',
                'perte de poids',
                'maintien',
                'prise de force',
                'endurance',
                'remise en forme',
                'sèche',
                'souplesse',
                'rééducation',
                'tonification',
                'préparation physique',
                'performance'
            ]),
            'sexe' => fake()->randomElement(['homme', 'femme']),
            'date_naissance' => fake()->date('Y-m-d', '2000-01-01'),
            'avatar' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
