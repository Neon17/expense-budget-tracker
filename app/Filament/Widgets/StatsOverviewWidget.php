<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Income;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $dataOwner = $user->getDataOwner();
        $currentMonth = now()->format('Y-m');
        $previousMonth = now()->subMonth()->format('Y-m');

        // Current month expenses (shared family data)
        $currentExpenses = Expense::whereIn('user_id', $userIds)
            ->month($currentMonth)
            ->sum('amount');
        $previousExpenses = Expense::whereIn('user_id', $userIds)
            ->month($previousMonth)
            ->sum('amount');
        $expenseChange = $this->calculateChange($previousExpenses, $currentExpenses);

        // Current month income (shared family data)
        $currentIncome = Income::whereIn('user_id', $userIds)
            ->month($currentMonth)
            ->sum('amount');
        $previousIncome = Income::whereIn('user_id', $userIds)
            ->month($previousMonth)
            ->sum('amount');
        $incomeChange = $this->calculateChange($previousIncome, $currentIncome);

        // Savings
        $savings = $currentIncome - $currentExpenses;
        $previousSavings = $previousIncome - $previousExpenses;
        $savingsChange = $this->calculateChange($previousSavings, $savings);

        // Budget (data owner's budget)
        $budget = Budget::where('user_id', $dataOwner->id)
            ->where('month', $currentMonth)
            ->first();

        $currency = $dataOwner->currency ?? 'NPR';

        return [
            Stat::make('Monthly Expenses', $currency . ' ' . number_format($currentExpenses, 2))
                ->description($expenseChange >= 0 ? "+{$expenseChange}% from last month" : "{$expenseChange}% from last month")
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseChange >= 0 ? 'danger' : 'success')
                ->chart($this->getExpenseChartData()),

            Stat::make('Monthly Income', $currency . ' ' . number_format($currentIncome, 2))
                ->description($incomeChange >= 0 ? "+{$incomeChange}% from last month" : "{$incomeChange}% from last month")
                ->descriptionIcon($incomeChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($incomeChange >= 0 ? 'success' : 'danger')
                ->chart($this->getIncomeChartData()),

            Stat::make('Monthly Savings', $currency . ' ' . number_format($savings, 2))
                ->description($savingsChange >= 0 ? "+{$savingsChange}% from last month" : "{$savingsChange}% from last month")
                ->descriptionIcon($savingsChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($savings >= 0 ? 'success' : 'danger'),

            Stat::make('Budget Status', $budget ? number_format($budget->usage_percentage, 1) . '%' : 'Not Set')
                ->description($budget ? "{$currency} {$budget->remaining} remaining" : 'Set your monthly budget')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($this->getBudgetColor($budget)),
        ];
    }

    protected function calculateChange(float $old, float $new): float
    {
        if ($old == 0) {
            return $new > 0 ? 100 : 0;
        }
        return round((($new - $old) / $old) * 100, 1);
    }

    protected function getBudgetColor($budget): string
    {
        if (!$budget) return 'gray';

        $percentage = $budget->usage_percentage;
        if ($percentage >= 100) return 'danger';
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 70) return 'warning';
        return 'success';
    }

    protected function getExpenseChartData(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $data[] = (float) Expense::whereIn('user_id', $userIds)->month($month)->sum('amount');
        }

        return $data;
    }

    protected function getIncomeChartData(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $data[] = (float) Income::whereIn('user_id', $userIds)->month($month)->sum('amount');
        }

        return $data;
    }
}
