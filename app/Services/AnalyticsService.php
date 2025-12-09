<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get monthly expense/income trend for a year.
     * Uses shared data for family members.
     */
    public function getMonthlyTrend(User $user, int $year): array
    {
        $userIds = $user->getSharedDashboardUserIds();
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $expenses = Expense::whereIn('user_id', $userIds)
                ->month($monthStr)
                ->sum('amount');

            $income = Income::whereIn('user_id', $userIds)
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
     * Uses shared data for family members.
     */
    public function getCategoryBreakdown(User $user, string $month): array
    {
        $userIds = $user->getSharedDashboardUserIds();
        
        $expenses = Expense::whereIn('user_id', $userIds)
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
     * Uses shared data for family members.
     */
    public function getBudgetVsActual(User $user, int $year): array
    {
        $dataOwner = $user->getDataOwner();
        $userIds = $user->getSharedDashboardUserIds();
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $budget = $dataOwner->budgets()
                ->where('month', $monthStr)
                ->first();

            $actual = Expense::whereIn('user_id', $userIds)
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
     * Uses shared data for family members.
     */
    public function getIncomeVsExpense(User $user, int $year): array
    {
        $userIds = $user->getSharedDashboardUserIds();
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);

            $income = Income::whereIn('user_id', $userIds)
                ->month($monthStr)
                ->sum('amount');

            $expense = Expense::whereIn('user_id', $userIds)
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
     * Uses shared data for family members.
     */
    public function getDashboardSummary(User $user): array
    {
        $userIds = $user->getSharedDashboardUserIds();
        $dataOwner = $user->getDataOwner();
        
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        // Current month stats
        $currentExpenses = Expense::whereIn('user_id', $userIds)->month($currentMonth)->sum('amount');
        $currentIncome = Income::whereIn('user_id', $userIds)->month($currentMonth)->sum('amount');

        // Previous month stats
        $previousExpenses = Expense::whereIn('user_id', $userIds)->month($previousMonth)->sum('amount');
        $previousIncome = Income::whereIn('user_id', $userIds)->month($previousMonth)->sum('amount');

        // Current budget (owned by data owner)
        $budget = $dataOwner->budgets()->where('month', $currentMonth)->first();

        // Recent transactions
        $recentExpenses = Expense::whereIn('user_id', $userIds)
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        $recentIncomes = Income::whereIn('user_id', $userIds)
            ->with('category')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        // Category breakdown for current month
        $topCategories = Expense::whereIn('user_id', $userIds)
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

        // Calculate budget stats with shared expenses
        $budgetData = null;
        if ($budget) {
            $spent = (float) $currentExpenses;
            $remaining = max(0, (float) $budget->monthly_limit - $spent);
            $usagePercentage = $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0;
            
            $budgetData = [
                'monthly_limit' => (float) $budget->monthly_limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'usage_percentage' => $usagePercentage,
                'status' => $this->getBudgetStatusFromPercentage($usagePercentage),
            ];
        }

        return [
            'current_month' => $currentMonth,
            'summary' => [
                'total_expenses' => (float) $currentExpenses,
                'total_income' => (float) $currentIncome,
                'savings' => (float) $currentIncome - (float) $currentExpenses,
                'expenses_change' => $this->calculatePercentageChange($previousExpenses, $currentExpenses),
                'income_change' => $this->calculatePercentageChange($previousIncome, $currentIncome),
            ],
            'budget' => $budgetData,
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

        return $this->getBudgetStatusFromPercentage($percentage);
    }

    /**
     * Get budget status from percentage.
     */
    protected function getBudgetStatusFromPercentage(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 90) {
            return 'critical';
        } elseif ($percentage >= 70) {
            return 'warning';
        }

        return 'safe';
    }

    /**
     * Get weekly expense/income stats for the specified number of weeks.
     * Uses shared data for family members.
     */
    public function getWeeklyStats(int $userId, int $weeksBack = 8): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();
        $weeks = [];

        for ($i = $weeksBack - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $expenses = Expense::whereIn('user_id', $userIds)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->sum('amount');

            $income = Income::whereIn('user_id', $userIds)
                ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->sum('amount');

            $weeks[] = [
                'week_number' => $weekStart->weekOfYear,
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'label' => $weekStart->format('M d') . ' - ' . $weekEnd->format('M d'),
                'expenses' => (float) $expenses,
                'income' => (float) $income,
                'net' => (float) $income - (float) $expenses,
                'transaction_count' => Expense::whereIn('user_id', $userIds)
                    ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->count() + Income::whereIn('user_id', $userIds)
                    ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->count(),
            ];
        }

        return [
            'weeks' => $weeks,
            'totals' => [
                'total_expenses' => collect($weeks)->sum('expenses'),
                'total_income' => collect($weeks)->sum('income'),
                'total_net' => collect($weeks)->sum('net'),
                'avg_weekly_expense' => collect($weeks)->avg('expenses'),
                'avg_weekly_income' => collect($weeks)->avg('income'),
            ],
        ];
    }

    /**
     * Get category-level statistics.
     * Uses shared data for family members.
     */
    public function getCategoryStats(int $userId, string $type = 'expense', ?string $startDate = null, ?string $endDate = null): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();
        
        $startDate = $startDate ?? now()->subMonths(6)->startOfMonth()->toDateString();
        $endDate = $endDate ?? now()->endOfMonth()->toDateString();

        if ($type === 'expense') {
            $query = Expense::whereIn('user_id', $userIds)->with('category');
        } else {
            $query = Income::whereIn('user_id', $userIds)->with('category');
        }

        $data = $query
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count, AVG(amount) as avg_amount, MIN(amount) as min_amount, MAX(amount) as max_amount')
            ->groupBy('category_id')
            ->get();

        $grandTotal = $data->sum('total');

        $categories = $data->map(function ($item) use ($grandTotal, $type) {
            $category = $item->category;
            return [
                'category_id' => $item->category_id,
                'category_name' => $category->name ?? 'Uncategorized',
                'category_icon' => $category->icon ?? 'heroicon-o-tag',
                'category_color' => $category->color ?? '#6366F1',
                'type' => $type,
                'total' => (float) $item->total,
                'count' => (int) $item->count,
                'avg_amount' => round((float) $item->avg_amount, 2),
                'min_amount' => (float) $item->min_amount,
                'max_amount' => (float) $item->max_amount,
                'percentage' => $grandTotal > 0 ? round(($item->total / $grandTotal) * 100, 2) : 0,
            ];
        })->sortByDesc('total')->values()->toArray();

        return [
            'type' => $type,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'categories' => $categories,
            'summary' => [
                'total_categories' => count($categories),
                'grand_total' => $grandTotal,
                'top_category' => $categories[0] ?? null,
            ],
        ];
    }

    /**
     * Get flat expense data for Superset integration.
     * Uses shared data for family members.
     */
    public function getSupersetExpenseData(int $userId, int $limit = 10000): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();

        return Expense::whereIn('user_id', $userIds)
            ->with(['category', 'user'])
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($expense) use ($user) {
                return [
                    'id' => $expense->id,
                    'user_id' => $expense->user_id,
                    'user_name' => $expense->user->name ?? $user->name,
                    'category_id' => $expense->category_id,
                    'category_name' => $expense->category->name ?? 'Uncategorized',
                    'category_color' => $expense->category->color ?? '#6366F1',
                    'amount' => (float) $expense->amount,
                    'description' => $expense->description,
                    'date' => $expense->date,
                    'year' => date('Y', strtotime($expense->date)),
                    'month' => date('m', strtotime($expense->date)),
                    'month_name' => date('F', strtotime($expense->date)),
                    'week' => date('W', strtotime($expense->date)),
                    'day_of_week' => date('l', strtotime($expense->date)),
                    'quarter' => ceil(date('n', strtotime($expense->date)) / 3),
                    'currency' => $user->currency ?? 'NPR',
                    'created_at' => $expense->created_at->toIso8601String(),
                ];
            })->toArray();
    }

    /**
     * Get flat income data for Superset integration.
     * Uses shared data for family members.
     */
    public function getSupersetIncomeData(int $userId, int $limit = 10000): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();

        return Income::whereIn('user_id', $userIds)
            ->with(['category', 'user'])
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($income) use ($user) {
                return [
                    'id' => $income->id,
                    'user_id' => $income->user_id,
                    'user_name' => $income->user->name ?? $user->name,
                    'category_id' => $income->category_id,
                    'category_name' => $income->category->name ?? 'Uncategorized',
                    'category_color' => $income->category->color ?? '#6366F1',
                    'amount' => (float) $income->amount,
                    'source' => $income->source,
                    'description' => $income->description,
                    'date' => $income->date,
                    'year' => date('Y', strtotime($income->date)),
                    'month' => date('m', strtotime($income->date)),
                    'month_name' => date('F', strtotime($income->date)),
                    'week' => date('W', strtotime($income->date)),
                    'day_of_week' => date('l', strtotime($income->date)),
                    'quarter' => ceil(date('n', strtotime($income->date)) / 3),
                    'currency' => $user->currency ?? 'NPR',
                    'is_recurring' => $income->is_recurring ?? false,
                    'created_at' => $income->created_at->toIso8601String(),
                ];
            })->toArray();
    }

    /**
     * Get monthly aggregate data for Superset charts.
     * Uses shared data for family members.
     */
    public function getSupersetMonthlyAggregate(int $userId, int $months = 24): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();
        $dataOwner = $user->getDataOwner();
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthStr = $monthDate->format('Y-m');

            $expenses = Expense::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');
            $income = Income::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');
            $budget = $dataOwner->budgets()->where('month', $monthStr)->first();

            // Get category breakdown for this month
            $categoryBreakdown = Expense::whereIn('user_id', $userIds)
                ->with('category')
                ->month($monthStr)
                ->selectRaw('category_id, SUM(amount) as total')
                ->groupBy('category_id')
                ->get()
                ->mapWithKeys(function ($item) {
                    $categoryName = $item->category->name ?? 'Uncategorized';
                    return [$categoryName => (float) $item->total];
                })->toArray();

            $data[] = [
                'month' => $monthStr,
                'month_name' => $monthDate->format('F'),
                'year' => $monthDate->year,
                'quarter' => ceil($monthDate->month / 3),
                'total_expenses' => (float) $expenses,
                'total_income' => (float) $income,
                'net_savings' => (float) $income - (float) $expenses,
                'savings_rate' => $income > 0 ? round((($income - $expenses) / $income) * 100, 2) : 0,
                'budget_amount' => $budget ? (float) $budget->monthly_limit : null,
                'budget_utilization' => $budget && $budget->monthly_limit > 0 
                    ? round(($expenses / $budget->monthly_limit) * 100, 2) 
                    : null,
                'expense_count' => Expense::whereIn('user_id', $userIds)->month($monthStr)->count(),
                'income_count' => Income::whereIn('user_id', $userIds)->month($monthStr)->count(),
                'category_breakdown' => $categoryBreakdown,
                'currency' => $user->currency ?? 'NPR',
            ];
        }

        return [
            'data' => $data,
            'meta' => [
                'user_id' => $userId,
                'months_included' => $months,
                'generated_at' => now()->toIso8601String(),
                'currency' => $user->currency ?? 'NPR',
            ],
        ];
    }

    /**
     * Get savings rate analysis.
     * Uses shared data for family members.
     */
    public function getSavingsRate(int $userId, int $months = 12): array
    {
        $user = User::findOrFail($userId);
        $userIds = $user->getSharedDashboardUserIds();
        $monthlyData = [];
        $totalIncome = 0;
        $totalExpenses = 0;

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $monthStr = $monthDate->format('Y-m');

            $income = Income::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');
            $expenses = Expense::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');

            $totalIncome += $income;
            $totalExpenses += $expenses;

            $savingsRate = $income > 0 ? round((($income - $expenses) / $income) * 100, 2) : 0;

            $monthlyData[] = [
                'month' => $monthStr,
                'month_name' => $monthDate->format('F Y'),
                'income' => (float) $income,
                'expenses' => (float) $expenses,
                'savings' => (float) $income - (float) $expenses,
                'savings_rate' => $savingsRate,
                'status' => $this->getSavingsStatus($savingsRate),
            ];
        }

        $overallSavingsRate = $totalIncome > 0 
            ? round((($totalIncome - $totalExpenses) / $totalIncome) * 100, 2) 
            : 0;

        return [
            'monthly_data' => $monthlyData,
            'summary' => [
                'period_months' => $months,
                'total_income' => (float) $totalIncome,
                'total_expenses' => (float) $totalExpenses,
                'total_savings' => (float) $totalIncome - (float) $totalExpenses,
                'overall_savings_rate' => $overallSavingsRate,
                'avg_monthly_income' => round($totalIncome / $months, 2),
                'avg_monthly_expenses' => round($totalExpenses / $months, 2),
                'avg_monthly_savings' => round(($totalIncome - $totalExpenses) / $months, 2),
                'best_month' => collect($monthlyData)->sortByDesc('savings_rate')->first(),
                'worst_month' => collect($monthlyData)->sortBy('savings_rate')->first(),
                'status' => $this->getSavingsStatus($overallSavingsRate),
                'recommendation' => $this->getSavingsRecommendation($overallSavingsRate),
            ],
            'benchmarks' => [
                'excellent' => 30,
                'good' => 20,
                'average' => 10,
                'poor' => 0,
            ],
        ];
    }

    /**
     * Get savings status based on rate.
     */
    protected function getSavingsStatus(float $rate): string
    {
        if ($rate >= 30) return 'excellent';
        if ($rate >= 20) return 'good';
        if ($rate >= 10) return 'average';
        if ($rate >= 0) return 'low';
        return 'negative';
    }

    /**
     * Get savings recommendation based on rate.
     */
    protected function getSavingsRecommendation(float $rate): string
    {
        if ($rate >= 30) {
            return 'Excellent savings rate! You\'re on track for financial independence. Consider investing the surplus.';
        }
        if ($rate >= 20) {
            return 'Good savings rate! You\'re building wealth steadily. Try to optimize your expenses to reach 30%.';
        }
        if ($rate >= 10) {
            return 'Average savings rate. Review your expenses and try to identify areas where you can cut back.';
        }
        if ($rate >= 0) {
            return 'Low savings rate. Consider creating a budget and reducing non-essential expenses.';
        }
        return 'Spending exceeds income! Urgent action needed. Review all expenses and find ways to increase income.';
    }
}
