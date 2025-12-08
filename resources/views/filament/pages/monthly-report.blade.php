<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Monthly Report - {{ $reportData['month'] }}
        </x-slot>

        <div class="mb-6">
            {{ $this->form }}
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-danger-600 dark:text-danger-400">
                        NPR {{ number_format($reportData['totalExpenses'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">Total Expenses</div>
                    <div class="text-xs text-gray-400">{{ $reportData['expenseCount'] }} transactions</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-600 dark:text-success-400">
                        NPR {{ number_format($reportData['totalIncome'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">Total Income</div>
                    <div class="text-xs text-gray-400">{{ $reportData['incomeCount'] }} transactions</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $reportData['savings'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                        NPR {{ number_format($reportData['savings'], 2) }}
                    </div>
                    <div class="text-sm text-gray-500">Net Savings</div>
                    <div class="text-xs text-gray-400">{{ $reportData['savingsRate'] }}% savings rate</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    @if($reportData['budget'])
                        <div class="text-2xl font-bold {{ $reportData['budgetUsage'] >= 100 ? 'text-danger-600 dark:text-danger-400' : ($reportData['budgetUsage'] >= 70 ? 'text-warning-600 dark:text-warning-400' : 'text-success-600 dark:text-success-400') }}">
                            {{ number_format($reportData['budgetUsage'], 1) }}%
                        </div>
                        <div class="text-sm text-gray-500">Budget Used</div>
                        <div class="text-xs text-gray-400">NPR {{ number_format($reportData['budget']->remaining, 2) }} remaining</div>
                    @else
                        <div class="text-2xl font-bold text-gray-400">N/A</div>
                        <div class="text-sm text-gray-500">No Budget Set</div>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Category Breakdown --}}
        <x-filament::section class="mb-6">
            <x-slot name="heading">
                Expense Breakdown by Category
            </x-slot>

            @if(count($reportData['categoryBreakdown']) > 0)
                <div class="space-y-4">
                    @foreach($reportData['categoryBreakdown'] as $category)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $category['color'] }}"></span>
                                    <span class="font-medium">{{ $category['name'] }}</span>
                                    <span class="text-xs text-gray-400">({{ $category['count'] }} items)</span>
                                </span>
                                <span class="font-semibold">NPR {{ number_format($category['total'], 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full" style="width: {{ $category['percentage'] }}%; background-color: {{ $category['color'] }}"></div>
                            </div>
                            <div class="text-xs text-gray-500 text-right">{{ $category['percentage'] }}%</div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No expenses recorded for this month.</p>
            @endif
        </x-filament::section>

        {{-- Top Expenses --}}
        <x-filament::section>
            <x-slot name="heading">
                Top 5 Expenses
            </x-slot>

            @if($reportData['topExpenses']->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2">Date</th>
                                <th class="text-left py-2">Category</th>
                                <th class="text-left py-2">Note</th>
                                <th class="text-right py-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['topExpenses'] as $expense)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2">{{ $expense->date->format('M d, Y') }}</td>
                                    <td class="py-2">
                                        <span class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full" style="background-color: {{ $expense->category?->color ?? '#6366F1' }}"></span>
                                            {{ $expense->category?->name ?? 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-gray-500">{{ Str::limit($expense->note, 40) ?? '-' }}</td>
                                    <td class="py-2 text-right font-semibold text-danger-600 dark:text-danger-400">
                                        NPR {{ number_format($expense->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No expenses recorded for this month.</p>
            @endif
        </x-filament::section>

        {{-- Summary Stats --}}
        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Quick Stats
            </x-slot>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-lg font-semibold">NPR {{ number_format($reportData['avgDailyExpense'], 2) }}</div>
                    <div class="text-xs text-gray-500">Avg. Daily Expense</div>
                </div>
                <div>
                    <div class="text-lg font-semibold">{{ count($reportData['dailySpending']) }}</div>
                    <div class="text-xs text-gray-500">Days with Expenses</div>
                </div>
                <div>
                    <div class="text-lg font-semibold">{{ count($reportData['categoryBreakdown']) }}</div>
                    <div class="text-xs text-gray-500">Categories Used</div>
                </div>
                <div>
                    <div class="text-lg font-semibold">{{ $reportData['expenseCount'] + $reportData['incomeCount'] }}</div>
                    <div class="text-xs text-gray-500">Total Transactions</div>
                </div>
            </div>
        </x-filament::section>
    </x-filament::section>
</x-filament-panels::page>
