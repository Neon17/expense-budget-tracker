<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncomeFactory extends Factory
{
    protected $model = Income::class;

    public function definition(): array
    {
        $sources = ['Salary', 'Freelance', 'Investments', 'Rental Income', 'Side Business', 'Bonus', 'Dividends'];

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => $this->faker->randomFloat(2, 5000, 100000),
            'source' => $this->faker->randomElement($sources),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'note' => $this->faker->optional()->sentence(),
            'currency' => 'NPR',
        ];
    }
}
