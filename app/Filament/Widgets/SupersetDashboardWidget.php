<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Income;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SupersetDashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.superset-dashboard';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public function getViewData(): array
    {
        $user = Auth::user();
        $currentMonth = now()->format('Y-m');

        // Get data for charts
        $monthlyData = $this->getMonthlyData($user);
        $categoryData = $this->getCategoryData($user, $currentMonth);
        $dailyTrend = $this->getDailyTrend($user);
        $incomeVsExpense = $this->getIncomeVsExpenseData($user);
        $savingsRate = $this->getSavingsRate($user);
        $weeklyPattern = $this->getWeeklyPattern($user);

        return [
            'monthlyData' => $monthlyData,
            'categoryData' => $categoryData,
            'dailyTrend' => $dailyTrend,
            'incomeVsExpense' => $incomeVsExpense,
            'savingsRate' => $savingsRate,
            'weeklyPattern' => $weeklyPattern,
            'currency' => $user->currency ?? 'NPR',
        ];
    }

    protected function getMonthlyData($user): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');
            $monthName = $date->format('M');

            $expenses = $user->expenses()->month($month)->sum('amount');
            $income = $user->incomes()->month($month)->sum('amount');

            $data[] = [
                'month' => $monthName,
                'expenses' => (float) $expenses,
                'income' => (float) $income,
                'savings' => (float) $income - (float) $expenses,
            ];
        }
        return $data;
    }

    protected function getCategoryData($user, $month): array
    {
        return $user->expenses()
            ->with('category')
            ->month($month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'value' => (float) $item->total,
                    'color' => $item->category->color ?? '#6366F1',
                ];
            })->toArray();
    }

    protected function getDailyTrend($user): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->toDateString();

            $expenses = $user->expenses()->whereDate('date', $dateStr)->sum('amount');

            $data[] = [
                'date' => $date->format('M d'),
                'amount' => (float) $expenses,
            ];
        }
        return $data;
    }

    protected function getIncomeVsExpenseData($user): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');

            $expenses = $user->expenses()->month($month)->sum('amount');
            $income = $user->incomes()->month($month)->sum('amount');

            $data[] = [
                'month' => $date->format('M'),
                'income' => (float) $income,
                'expense' => (float) $expenses,
            ];
        }
        return $data;
    }

    protected function getSavingsRate($user): array
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = $date->format('Y-m');

            $income = $user->incomes()->month($month)->sum('amount');
            $expenses = $user->expenses()->month($month)->sum('amount');
            $rate = $income > 0 ? round((($income - $expenses) / $income) * 100, 1) : 0;

            $data[] = [
                'month' => $date->format('M'),
                'rate' => $rate,
            ];
        }
        return $data;
    }

    protected function getWeeklyPattern($user): array
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $data = [];

        // Get last 30 days of expenses grouped by day of week
        $expenses = $user->expenses()
            ->where('date', '>=', now()->subDays(30)->toDateString())
            ->get()
            ->groupBy(function ($expense) {
                return date('w', strtotime($expense->date));
            });

        foreach ($days as $index => $day) {
            $dayExpenses = $expenses->get($index, collect());
            $data[] = [
                'day' => $day,
                'total' => (float) $dayExpenses->sum('amount'),
                'count' => $dayExpenses->count(),
            ];
        }

        return $data;
    }
}
