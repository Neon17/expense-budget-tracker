<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo user
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
            'currency' => 'NPR',
        ]);

        // Create expense categories
        $expenseCategories = [
            ['name' => 'Food & Dining', 'color' => '#EF4444', 'icon' => 'heroicon-o-shopping-bag', 'type' => 'expense'],
            ['name' => 'Transportation', 'color' => '#F97316', 'icon' => 'heroicon-o-truck', 'type' => 'expense'],
            ['name' => 'Shopping', 'color' => '#F59E0B', 'icon' => 'heroicon-o-shopping-cart', 'type' => 'expense'],
            ['name' => 'Entertainment', 'color' => '#8B5CF6', 'icon' => 'heroicon-o-film', 'type' => 'expense'],
            ['name' => 'Bills & Utilities', 'color' => '#06B6D4', 'icon' => 'heroicon-o-document-text', 'type' => 'expense'],
            ['name' => 'Healthcare', 'color' => '#EC4899', 'icon' => 'heroicon-o-heart', 'type' => 'expense'],
            ['name' => 'Education', 'color' => '#3B82F6', 'icon' => 'heroicon-o-academic-cap', 'type' => 'expense'],
            ['name' => 'Others', 'color' => '#6B7280', 'icon' => 'heroicon-o-ellipsis-horizontal', 'type' => 'expense'],
        ];

        $createdExpenseCategories = [];
        foreach ($expenseCategories as $category) {
            $createdExpenseCategories[] = Category::create([
                'user_id' => $user->id,
                ...$category,
            ]);
        }

        // Create income categories
        $incomeCategories = [
            ['name' => 'Salary', 'color' => '#22C55E', 'icon' => 'heroicon-o-banknotes', 'type' => 'income'],
            ['name' => 'Freelance', 'color' => '#14B8A6', 'icon' => 'heroicon-o-briefcase', 'type' => 'income'],
            ['name' => 'Investments', 'color' => '#6366F1', 'icon' => 'heroicon-o-chart-bar', 'type' => 'income'],
            ['name' => 'Other Income', 'color' => '#84CC16', 'icon' => 'heroicon-o-plus-circle', 'type' => 'income'],
        ];

        $createdIncomeCategories = [];
        foreach ($incomeCategories as $category) {
            $createdIncomeCategories[] = Category::create([
                'user_id' => $user->id,
                ...$category,
            ]);
        }

        // Create expenses for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $expenseCount = rand(15, 25);

            for ($j = 0; $j < $expenseCount; $j++) {
                Expense::create([
                    'user_id' => $user->id,
                    'category_id' => $createdExpenseCategories[array_rand($createdExpenseCategories)]->id,
                    'amount' => rand(100, 5000),
                    'date' => $month->copy()->day(rand(1, $month->daysInMonth)),
                    'note' => fake()->optional()->sentence(),
                    'currency' => 'NPR',
                ]);
            }
        }

        // Create incomes for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            // Main salary
            Income::create([
                'user_id' => $user->id,
                'category_id' => $createdIncomeCategories[0]->id, // Salary
                'amount' => rand(40000, 60000),
                'source' => 'Monthly Salary',
                'date' => $month->copy()->day(1),
                'currency' => 'NPR',
            ]);

            // Random additional income
            if (rand(0, 1)) {
                Income::create([
                    'user_id' => $user->id,
                    'category_id' => $createdIncomeCategories[rand(1, 3)]->id,
                    'amount' => rand(5000, 20000),
                    'source' => fake()->randomElement(['Freelance Project', 'Investment Returns', 'Side Gig', 'Bonus']),
                    'date' => $month->copy()->day(rand(10, 25)),
                    'currency' => 'NPR',
                ]);
            }
        }

        // Create budgets for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            Budget::create([
                'user_id' => $user->id,
                'monthly_limit' => rand(30000, 50000),
                'month' => $month,
                'currency' => 'NPR',
            ]);
        }

        $this->command->info('Demo data created successfully!');
        $this->command->info('Login with: demo@example.com / password');
    }
}
