<?php

namespace App\Filament\Widgets;

use App\Models\Budget;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BudgetProgressWidget extends ChartWidget
{
    protected ?string $heading = 'Budget vs Actual (This Year)';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $dataOwner = $user->getDataOwner();
        $year = now()->year;
        $currentMonth = now()->month;

        $budgets = [];
        $actuals = [];
        $labels = [];

        for ($month = 1; $month <= $currentMonth; $month++) {
            $monthStr = sprintf('%d-%02d', $year, $month);
            $labels[] = date('M', mktime(0, 0, 0, $month, 1));

            $budget = Budget::where('user_id', $dataOwner->id)
                ->where('month', $monthStr)
                ->first();
            $budgets[] = $budget ? (float) $budget->monthly_limit : 0;

            $actuals[] = (float) Expense::whereIn('user_id', $userIds)
                ->month($monthStr)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Budget',
                    'data' => $budgets,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Actual Spending',
                    'data' => $actuals,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
