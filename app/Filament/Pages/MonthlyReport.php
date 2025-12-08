<?php

namespace App\Filament\Pages;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use UnitEnum;

class MonthlyReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'Monthly Report';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.monthly-report';

    public ?string $selectedMonth = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->components([
                Select::make('selectedMonth')
                    ->label('Select Month')
                    ->options($this->getMonthOptions())
                    ->default(now()->format('Y-m'))
                    ->reactive()
                    ->afterStateUpdated(fn () => $this->getReportData()),
            ]);
    }

    protected function getMonthOptions(): array
    {
        $options = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $options[$date->format('Y-m')] = $date->format('F Y');
        }
        return $options;
    }

    public function getReportData(): array
    {
        $user = auth()->user();
        $month = $this->selectedMonth ?? now()->format('Y-m');

        $expenses = Expense::where('user_id', $user->id)
            ->month($month)
            ->get();

        $incomes = Income::where('user_id', $user->id)
            ->month($month)
            ->get();

        $budget = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->first();

        $totalExpenses = $expenses->sum('amount');
        $totalIncome = $incomes->sum('amount');
        $savings = $totalIncome - $totalExpenses;

        // Category breakdown
        $categoryBreakdown = $expenses->groupBy('category_id')->map(function ($items, $categoryId) use ($totalExpenses) {
            $category = Category::find($categoryId);
            $total = $items->sum('amount');
            return [
                'name' => $category?->name ?? 'Uncategorized',
                'color' => $category?->color ?? '#6366F1',
                'total' => $total,
                'percentage' => $totalExpenses > 0 ? round(($total / $totalExpenses) * 100, 1) : 0,
                'count' => $items->count(),
            ];
        })->sortByDesc('total')->values()->toArray();

        // Daily spending
        $dailySpending = $expenses->groupBy(fn ($e) => $e->date->format('Y-m-d'))
            ->map(fn ($items) => $items->sum('amount'))
            ->toArray();

        // Top expenses
        $topExpenses = $expenses->sortByDesc('amount')->take(5)->values();

        return [
            'month' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'savings' => $savings,
            'savingsRate' => $totalIncome > 0 ? round(($savings / $totalIncome) * 100, 1) : 0,
            'budget' => $budget,
            'budgetUsage' => $budget ? $budget->usage_percentage : null,
            'categoryBreakdown' => $categoryBreakdown,
            'dailySpending' => $dailySpending,
            'topExpenses' => $topExpenses,
            'expenseCount' => $expenses->count(),
            'incomeCount' => $incomes->count(),
            'avgDailyExpense' => $expenses->count() > 0 ? round($totalExpenses / max(1, count($dailySpending)), 2) : 0,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'reportData' => $this->getReportData(),
        ];
    }
}
