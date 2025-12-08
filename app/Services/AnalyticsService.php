<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get monthly expense/income trend for a year.
     */
    public function getMonthlyTrend(User $user, int $year): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $expenses = $user->expenses()
                ->month($monthStr)
                ->sum('amount');

            $income = $user->incomes()
                ->month($monthStr)
                ->sum('amount');

            $months[] = [
                'month' => $monthStr,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'expenses' => (float) $expenses,
                'income' => (float) $income,
                'savings' => (float) $income - (float) $expenses,
            ];
        }

        return $months;
    }

    /**
     * Get expense breakdown by category for a month.
     */
    public function getCategoryBreakdown(User $user, string $month): array
    {
        $expenses = $user->expenses()
            ->with('category')
            ->month($month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get();

        $total = $expenses->sum('total');

        return $expenses->map(function ($expense) use ($total) {
            return [
                'category_id' => $expense->category_id,
                'category_name' => $expense->category->name ?? 'Uncategorized',
                'category_color' => $expense->category->color ?? '#6366F1',
                'amount' => (float) $expense->total,
                'percentage' => $total > 0 ? round(($expense->total / $total) * 100, 2) : 0,
            ];
        })->toArray();
    }

    /**
     * Get budget vs actual comparison for a year.
     */
    public function getBudgetVsActual(User $user, int $year): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $budget = $user->budgets()
                ->where('month', $monthStr)
                ->first();

            $actual = $user->expenses()
                ->month($monthStr)
                ->sum('amount');

            $data[] = [
                'month' => $monthStr,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'budget' => $budget ? (float) $budget->monthly_limit : 0,
                'actual' => (float) $actual,
                'difference' => $budget ? ((float) $budget->monthly_limit - (float) $actual) : (float) -$actual,
                'status' => $this->getBudgetStatus($budget, $actual),
            ];
        }

        return $data;
    }

    /**
     * Get income vs expense comparison for a year.
     */
    public function getIncomeVsExpense(User $user, int $year): array
    {
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $income = $user->incomes()
                ->month($monthStr)
                ->sum('amount');

            $expense = $user->expenses()
                ->month($monthStr)
                ->sum('amount');

            $data[] = [
                'month' => $monthStr,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'net' => (float) $income - (float) $expense,
            ];
        }

        return $data;
    }

    /**
     * Get dashboard summary.
     */
    public function getDashboardSummary(User $user): array
    {
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        // Current month stats
        $currentExpenses = $user->expenses()->month($currentMonth)->sum('amount');
        $currentIncome = $user->incomes()->month($currentMonth)->sum('amount');

        // Previous month stats
        $previousExpenses = $user->expenses()->month($previousMonth)->sum('amount');
        $previousIncome = $user->incomes()->month($previousMonth)->sum('amount');

        // Current budget
        $budget = $user->budgets()->where('month', $currentMonth)->first();

        // Recent transactions
        $recentExpenses = $user->expenses()
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        $recentIncomes = $user->incomes()
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Category breakdown for current month
        $topCategories = $user->expenses()
            ->with('category')
            ->month($currentMonth)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total' => (float) $item->total,
                ];
            });

        return [
            'current_month' => $currentMonth,
            'summary' => [
                'total_expenses' => (float) $currentExpenses,
                'total_income' => (float) $currentIncome,
                'savings' => (float) $currentIncome - (float) $currentExpenses,
                'expenses_change' => $this->calculatePercentageChange($previousExpenses, $currentExpenses),
                'income_change' => $this->calculatePercentageChange($previousIncome, $currentIncome),
            ],
            'budget' => $budget ? [
                'monthly_limit' => (float) $budget->monthly_limit,
                'spent' => (float) $budget->spent,
                'remaining' => (float) $budget->remaining,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ] : null,
            'top_categories' => $topCategories,
            'recent_expenses' => $recentExpenses,
            'recent_incomes' => $recentIncomes,
        ];
    }

    /**
     * Calculate percentage change between two values.
     */
    protected function calculatePercentageChange(float $old, float $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }

        return round((($new - $old) / $old) * 100, 2);
    }

    /**
     * Get budget status based on spending.
     */
    protected function getBudgetStatus(?Budget $budget, float $actual): string
    {
        if (!$budget) {
            return 'no_budget';
        }

        $percentage = ($actual / $budget->monthly_limit) * 100;

        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 90) {
            return 'critical';
        } elseif ($percentage >= 70) {
            return 'warning';
        }

        return 'safe';
    }
}
