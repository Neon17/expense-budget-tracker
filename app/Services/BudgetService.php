<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;

class BudgetService
{
    /**
     * Get or create budget for current month.
     */
    public function getOrCreateCurrentBudget(User $user, float $monthlyLimit = null): Budget
    {
        $currentMonth = now()->format('Y-m');

        $budget = $user->budgets()->where('month', $currentMonth)->first();

        if (!$budget && $monthlyLimit) {
            $budget = $user->budgets()->create([
                'monthly_limit' => $monthlyLimit,
                'month' => $currentMonth,
                'currency' => $user->currency ?? 'NPR',
            ]);
        }

        return $budget;
    }

    /**
     * Update budget for a specific month.
     */
    public function updateBudget(User $user, string $month, float $monthlyLimit): Budget
    {
        return $user->budgets()->updateOrCreate(
            ['month' => $month],
            [
                'monthly_limit' => $monthlyLimit,
                'currency' => $user->currency ?? 'NPR',
            ]
        );
    }

    /**
     * Get budget status details.
     */
    public function getBudgetStatus(Budget $budget): array
    {
        $spent = $budget->spent;
        $remaining = $budget->remaining;
        $percentage = $budget->usage_percentage;

        return [
            'monthly_limit' => (float) $budget->monthly_limit,
            'spent' => (float) $spent,
            'remaining' => (float) $remaining,
            'usage_percentage' => $percentage,
            'status' => $budget->status,
            'alerts' => $this->getAlerts($percentage),
        ];
    }

    /**
     * Get alerts based on budget usage percentage.
     */
    protected function getAlerts(float $percentage): array
    {
        $alerts = [];

        if ($percentage >= 100) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'You have exceeded your monthly budget!',
            ];
        } elseif ($percentage >= 90) {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Warning: You have used 90% of your budget.',
            ];
        } elseif ($percentage >= 70) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Caution: You have used 70% of your budget.',
            ];
        }

        return $alerts;
    }

    /**
     * Check if adding an expense would exceed budget.
     */
    public function wouldExceedBudget(User $user, float $amount): array
    {
        $budget = Budget::currentForUser($user);

        if (!$budget) {
            return [
                'would_exceed' => false,
                'has_budget' => false,
            ];
        }

        $projectedSpent = $budget->spent + $amount;
        $wouldExceed = $projectedSpent > $budget->monthly_limit;

        return [
            'would_exceed' => $wouldExceed,
            'has_budget' => true,
            'current_spent' => (float) $budget->spent,
            'projected_spent' => (float) $projectedSpent,
            'budget_limit' => (float) $budget->monthly_limit,
            'projected_percentage' => round(($projectedSpent / $budget->monthly_limit) * 100, 2),
        ];
    }
}
