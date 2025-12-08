<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'monthly_limit' => $this->faker->randomFloat(2, 20000, 100000),
            'month' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m'),
            'currency' => 'NPR',
        ];
    }
}
