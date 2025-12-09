<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Income;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ExpenseChartWidget extends ChartWidget
{
    protected ?string $heading = 'Expenses vs Income (Last 6 Months)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $expenses = [];
        $incomes = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStr = $month->format('Y-m');
            $labels[] = $month->format('M Y');

            $expenses[] = (float) Expense::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');
            $incomes[] = (float) Income::whereIn('user_id', $userIds)->month($monthStr)->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Expenses',
                    'data' => $expenses,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
                [
                    'label' => 'Income',
                    'data' => $incomes,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
