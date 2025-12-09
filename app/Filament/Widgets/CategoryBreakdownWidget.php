<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class CategoryBreakdownWidget extends ChartWidget
{
    protected ?string $heading = 'Expense Breakdown by Category';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $user = Auth::user();
        $userIds = $user->getSharedDashboardUserIds();
        $currentMonth = now()->format('Y-m');

        $categoryData = Expense::whereIn('user_id', $userIds)
            ->with('category')
            ->month($currentMonth)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($categoryData as $item) {
            $labels[] = $item->category->name ?? 'Uncategorized';
            $data[] = (float) $item->total;
            $colors[] = $item->category->color ?? '#6366F1';
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                ],
            ],
        ];
    }
}
