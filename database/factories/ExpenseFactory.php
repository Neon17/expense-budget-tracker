<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $this->faker->optional()->sentence(),
            'currency' => 'NPR',
        ];
    }
}
