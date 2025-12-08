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
use Illuminate\Support\Facades\Auth;
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
        $user = Auth::user();
        $month = $this->selectedMonth ?? now()->format('Y-m');

        $expenses = Expense::where('user_id', $user->id)
            ->month($month)
            ->get();

        $incomes = Income::where('user_id', $user->id)
            ->month($month)
            ->get();

        // Get all budgets for the user (for the selected month)
        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $totalIncome = $incomes->sum('amount');
        $netBalance = $totalIncome - $totalExpenses;

        // Category breakdown (for expensesByCategory)
        $expensesByCategory = $expenses->groupBy('category_id')->map(function ($items, $categoryId) use ($totalExpenses) {
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

        // Recent expenses (for recentExpenses)
        $recentExpenses = $expenses->sortByDesc('date')->take(5)->map(function ($expense) {
            return [
                'description' => $expense->description,
                'amount' => $expense->amount,
                'category' => $expense->category?->name ?? 'Uncategorized',
                'date' => $expense->date->format('M d, Y'),
            ];
        })->values()->toArray();

        // Recent incomes (for recentIncomes)
        $recentIncomes = $incomes->sortByDesc('date')->take(5)->map(function ($income) {
            return [
                'source' => $income->source ?? $income->description ?? 'Income',
                'amount' => $income->amount,
                'date' => $income->date->format('M d, Y'),
            ];
        })->values()->toArray();

        // Format budgets for the view
        $budgetsFormatted = $budgets->map(function ($budget) use ($expenses) {
            $spent = $expenses->sum('amount');
            
            return [
                'category' => 'Overall Budget',
                'amount' => $budget->amount,
                'spent' => $spent,
                'period' => $budget->period,
            ];
        })->toArray();

        return [
            'month' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'netBalance' => $netBalance,
            'savings' => $netBalance,
            'savingsRate' => $totalIncome > 0 ? round(($netBalance / $totalIncome) * 100, 1) : 0,
            'budgets' => $budgetsFormatted,
            'budgetUsage' => count($budgetsFormatted) > 0 ? $budgetsFormatted[0] : null,
            'expensesByCategory' => $expensesByCategory,
            'categoryBreakdown' => $expensesByCategory,
            'dailySpending' => $dailySpending,
            'topExpenses' => $topExpenses,
            'recentExpenses' => $recentExpenses,
            'recentIncomes' => $recentIncomes,
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
