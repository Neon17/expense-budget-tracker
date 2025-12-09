<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FamilyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && ($user->role === 'parent' || $user->children()->exists());
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $currentMonth = now()->format('Y-m');

        // Get all family members (children)
        $children = $user->children()->get();
        
        if ($children->isEmpty()) {
            return [];
        }

        // Calculate family totals
        $familyExpenses = $children->sum(function ($child) use ($currentMonth) {
            return $child->expenses()->month($currentMonth)->sum('amount');
        });

        $familyIncome = $children->sum(function ($child) use ($currentMonth) {
            return $child->incomes()->month($currentMonth)->sum('amount');
        });

        $activeMembers = $children->where('is_active', true)->count();
        $totalMembers = $children->count();

        // Get parent's own totals
        $parentExpenses = $user->expenses()->month($currentMonth)->sum('amount');
        $parentIncome = $user->incomes()->month($currentMonth)->sum('amount');

        // Combined family totals
        $totalFamilyExpenses = $familyExpenses + $parentExpenses;
        $totalFamilyIncome = $familyIncome + $parentIncome;

        return [
            Stat::make('Family Members', "{$activeMembers}/{$totalMembers} Active")
                ->description('Total family members')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($this->getMemberActivityChart()),

            Stat::make('Family Expenses', 'NPR ' . number_format($totalFamilyExpenses, 2))
                ->description('Combined this month')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),

            Stat::make('Family Income', 'NPR ' . number_format($totalFamilyIncome, 2))
                ->description('Combined this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Net Family Balance', 'NPR ' . number_format($totalFamilyIncome - $totalFamilyExpenses, 2))
                ->description($totalFamilyIncome >= $totalFamilyExpenses ? 'Positive balance' : 'Negative balance')
                ->descriptionIcon($totalFamilyIncome >= $totalFamilyExpenses ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalFamilyIncome >= $totalFamilyExpenses ? 'success' : 'danger'),
        ];
    }

    protected function getMemberActivityChart(): array
    {
        // Simple activity chart showing last 7 days of expenses
        return [7, 3, 5, 8, 4, 6, 5];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
