<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\FamilyGroup;
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
        // Create demo user (standalone user - no family)
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password'),
            'currency' => 'NPR',
            'role' => 'user',
        ]);

        // Create parent user with family
        $parentUser = User::factory()->create([
            'name' => 'John Smith (Parent)',
            'email' => 'parent@example.com',
            'password' => Hash::make('password'),
            'currency' => 'NPR',
            'role' => 'parent',
            'family_name' => 'Smith Family',
        ]);

        // Create family group for the parent
        $familyGroup = FamilyGroup::create([
            'name' => 'Smith Family Budget',
            'description' => 'Shared family budget for the Smith family',
            'owner_id' => $parentUser->id,
            'currency' => 'NPR',
            'shared_budget' => 100000,
            'is_active' => true,
        ]);

        // Create child users under the parent
        $childUser1 = User::factory()->create([
            'name' => 'Sarah Smith (Child)',
            'email' => 'child1@example.com',
            'password' => Hash::make('password'),
            'currency' => 'NPR',
            'role' => 'child',
            'parent_id' => $parentUser->id,
            'family_name' => 'Smith Family',
        ]);

        $childUser2 = User::factory()->create([
            'name' => 'Mike Smith (Child)',
            'email' => 'child2@example.com',
            'password' => Hash::make('password'),
            'currency' => 'NPR',
            'role' => 'child',
            'parent_id' => $parentUser->id,
            'family_name' => 'Smith Family',
        ]);

        // Add children to family group as members
        $familyGroup->addMember($childUser1->id, 'member');
        $familyGroup->addMember($childUser2->id, 'member');

        // Create another parent user with family
        $parentUser2 = User::factory()->create([
            'name' => 'Emily Johnson (Parent)',
            'email' => 'parent2@example.com',
            'password' => Hash::make('password'),
            'currency' => 'USD',
            'role' => 'parent',
            'family_name' => 'Johnson Family',
        ]);

        // Create family group for second parent
        $familyGroup2 = FamilyGroup::create([
            'name' => 'Johnson Family Budget',
            'description' => 'Shared family budget for the Johnson family',
            'owner_id' => $parentUser2->id,
            'currency' => 'USD',
            'shared_budget' => 5000,
            'is_active' => true,
        ]);

        // Create child for second family
        $childUser3 = User::factory()->create([
            'name' => 'Tom Johnson (Child)',
            'email' => 'child3@example.com',
            'password' => Hash::make('password'),
            'currency' => 'USD',
            'role' => 'child',
            'parent_id' => $parentUser2->id,
            'family_name' => 'Johnson Family',
        ]);

        $familyGroup2->addMember($childUser3->id, 'member');

        // Helper function to create categories and data for a user
        $this->createUserData($user, 'NPR');
        $this->createUserData($parentUser, 'NPR');
        $this->createUserData($parentUser2, 'USD');

        // Create some expenses/incomes for child users (on parent's dashboard)
        $this->createChildData($childUser1, $parentUser, 'NPR');
        $this->createChildData($childUser2, $parentUser, 'NPR');
        $this->createChildData($childUser3, $parentUser2, 'USD');

        $this->command->info('Demo data created successfully!');
        $this->command->info('');
        $this->command->info('=== Login Credentials ===');
        $this->command->info('');
        $this->command->info('Standalone User:');
        $this->command->info('  demo@example.com / password');
        $this->command->info('');
        $this->command->info('Smith Family (NPR):');
        $this->command->info('  Parent: parent@example.com / password');
        $this->command->info('  Child 1: child1@example.com / password');
        $this->command->info('  Child 2: child2@example.com / password');
        $this->command->info('');
        $this->command->info('Johnson Family (USD):');
        $this->command->info('  Parent: parent2@example.com / password');
        $this->command->info('  Child: child3@example.com / password');
    }

    /**
     * Create categories, expenses, incomes, and budgets for a user.
     */
    private function createUserData(User $user, string $currency): void
    {
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

        // Amount multiplier based on currency
        $multiplier = $currency === 'USD' ? 1 : 130;

        // Create expenses for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $expenseCount = rand(15, 25);

            for ($j = 0; $j < $expenseCount; $j++) {
                Expense::create([
                    'user_id' => $user->id,
                    'category_id' => $createdExpenseCategories[array_rand($createdExpenseCategories)]->id,
                    'amount' => rand(1, 50) * $multiplier,
                    'date' => $month->copy()->day(rand(1, $month->daysInMonth)),
                    'note' => fake()->optional()->sentence(),
                    'currency' => $currency,
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
                'amount' => rand(400, 600) * $multiplier,
                'source' => 'Monthly Salary',
                'date' => $month->copy()->day(1),
                'currency' => $currency,
            ]);

            // Random additional income
            if (rand(0, 1)) {
                Income::create([
                    'user_id' => $user->id,
                    'category_id' => $createdIncomeCategories[rand(1, 3)]->id,
                    'amount' => rand(50, 200) * $multiplier,
                    'source' => fake()->randomElement(['Freelance Project', 'Investment Returns', 'Side Gig', 'Bonus']),
                    'date' => $month->copy()->day(rand(10, 25)),
                    'currency' => $currency,
                ]);
            }
        }

        // Create budgets for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            Budget::create([
                'user_id' => $user->id,
                'monthly_limit' => rand(300, 500) * $multiplier,
                'month' => $month,
                'currency' => $currency,
            ]);
        }
    }

    /**
     * Create some expenses and incomes for child users.
     * These will show up on the parent's shared dashboard.
     */
    private function createChildData(User $child, User $parent, string $currency): void
    {
        // Get parent's categories (child uses parent's categories)
        $expenseCategories = Category::where('user_id', $parent->id)->where('type', 'expense')->get();
        $incomeCategories = Category::where('user_id', $parent->id)->where('type', 'income')->get();

        if ($expenseCategories->isEmpty() || $incomeCategories->isEmpty()) {
            return;
        }

        // Amount multiplier based on currency
        $multiplier = $currency === 'USD' ? 1 : 130;

        // Create some expenses for child (last 3 months)
        for ($i = 2; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $expenseCount = rand(5, 10);

            for ($j = 0; $j < $expenseCount; $j++) {
                Expense::create([
                    'user_id' => $child->id,
                    'category_id' => $expenseCategories->random()->id,
                    'amount' => rand(1, 30) * $multiplier,
                    'date' => $month->copy()->day(rand(1, $month->daysInMonth)),
                    'note' => fake()->optional()->sentence() . ' (by ' . $child->name . ')',
                    'currency' => $currency,
                ]);
            }
        }

        // Create some incomes for child (allowance, part-time job, etc.)
        for ($i = 2; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            // Allowance or part-time income
            if (rand(0, 1)) {
                Income::create([
                    'user_id' => $child->id,
                    'category_id' => $incomeCategories->last()->id, // Other Income
                    'amount' => rand(10, 50) * $multiplier,
                    'source' => fake()->randomElement(['Allowance', 'Part-time Job', 'Gift', 'Tutoring']),
                    'date' => $month->copy()->day(rand(1, 15)),
                    'currency' => $currency,
                ]);
            }
        }
    }
}
