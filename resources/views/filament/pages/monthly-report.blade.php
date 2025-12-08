<x-filament-panels::page>
    {{-- Month Selector --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- Header with Month Info --}}
    <div class="bg-gradient-to-r from-primary-500 to-primary-700 dark:from-primary-600 dark:to-primary-800 rounded-xl p-6 mb-6 text-white shadow-lg">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold">{{ $reportData['month'] }}</h2>
                <p class="text-primary-100 mt-1">Financial Overview & Analysis</p>
            </div>
            <div class="flex items-center gap-2 bg-white/20 rounded-lg px-4 py-2">
                <span>{{ $reportData['expenseCount'] + $reportData['incomeCount'] }} Transactions</span>
            </div>
        </div>
    </div>

    {{-- Main Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Expenses --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-red-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                NPR {{ number_format($reportData['totalExpenses'], 2) }}
            </div>
            <div class="mt-2 text-sm text-gray-500">{{ $reportData['expenseCount'] }} transactions</div>
        </div>

        {{-- Total Income --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-green-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                NPR {{ number_format($reportData['totalIncome'], 2) }}
            </div>
            <div class="mt-2 text-sm text-gray-500">{{ $reportData['incomeCount'] }} transactions</div>
        </div>

        {{-- Net Balance --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-blue-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Balance</span>
            </div>
            <div class="text-2xl font-bold {{ $reportData['netBalance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                NPR {{ number_format($reportData['netBalance'], 2) }}
            </div>
            <div class="mt-2 text-sm text-gray-500">
                {{ $reportData['netBalance'] >= 0 ? 'Surplus' : 'Deficit' }}
            </div>
        </div>

        {{-- Savings Rate --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-purple-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Savings Rate</span>
            </div>
            @php
                $savingsRate = $reportData['totalIncome'] > 0 
                    ? (($reportData['totalIncome'] - $reportData['totalExpenses']) / $reportData['totalIncome']) * 100 
                    : 0;
            @endphp
            <div class="text-2xl font-bold {{ $savingsRate >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                {{ number_format($savingsRate, 1) }}%
            </div>
            <div class="mt-2 text-sm text-gray-500">
                {{ $savingsRate >= 20 ? 'Excellent' : ($savingsRate >= 10 ? 'Good' : ($savingsRate >= 0 ? 'Could improve' : 'Overspending')) }}
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Expense vs Income Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                Income vs Expenses
            </h3>
            <div class="h-64 relative">
                <canvas id="income-expense-chart"></canvas>
            </div>
        </div>

        {{-- Category Breakdown Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                Expense by Category
            </h3>
            <div class="h-64 relative">
                <canvas id="category-chart"></canvas>
            </div>
        </div>
    </div>

    {{-- Budget Status Section --}}
    @if(count($reportData['budgets']) > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            Budget Status
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($reportData['budgets'] as $budget)
                @php
                    $percentage = $budget['amount'] > 0 ? ($budget['spent'] / $budget['amount']) * 100 : 0;
                    $remaining = $budget['amount'] - $budget['spent'];
                    $statusColor = $percentage >= 100 ? 'red' : ($percentage >= 80 ? 'yellow' : 'green');
                @endphp
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $budget['category'] }}</h4>
                            <p class="text-sm text-gray-500">{{ ucfirst($budget['period']) }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $statusColor === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                            {{ $statusColor === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                            {{ $statusColor === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}">
                            {{ number_format($percentage, 0) }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                        <div class="h-2 rounded-full transition-all duration-300
                            {{ $statusColor === 'red' ? 'bg-red-500' : '' }}
                            {{ $statusColor === 'yellow' ? 'bg-yellow-500' : '' }}
                            {{ $statusColor === 'green' ? 'bg-green-500' : '' }}" 
                            style="width: {{ min($percentage, 100) }}%">
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">NPR {{ number_format($budget['spent'], 0) }} spent</span>
                        <span class="text-gray-500">NPR {{ number_format($budget['amount'], 0) }} budget</span>
                    </div>
                    @if($remaining < 0)
                        <p class="text-xs text-red-500 mt-1">Over budget by NPR {{ number_format(abs($remaining), 0) }}</p>
                    @else
                        <p class="text-xs text-green-500 mt-1">NPR {{ number_format($remaining, 0) }} remaining</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Category Breakdown Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            Category Breakdown
        </h3>
        @if(count($reportData['expensesByCategory']) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Category</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Amount</th>
                            <th class="text-right py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">%</th>
                            <th class="text-left py-3 px-4 text-sm font-medium text-gray-500 dark:text-gray-400">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData['expensesByCategory'] as $category)
                            @php
                                $percentage = $reportData['totalExpenses'] > 0 
                                    ? ($category['total'] / $reportData['totalExpenses']) * 100 
                                    : 0;
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $category['color'] ?? '#6366f1' }}"></div>
                                        <span class="text-gray-900 dark:text-white">{{ $category['name'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right text-gray-900 dark:text-white font-medium">
                                    NPR {{ number_format($category['total'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right text-gray-500">
                                    {{ number_format($percentage, 1) }}%
                                </td>
                                <td class="py-3 px-4">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-primary-500" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No expense data for this month</p>
        @endif
    </div>

    {{-- Recent Transactions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Expenses --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                Recent Expenses
            </h3>
            @if(count($reportData['recentExpenses']) > 0)
                <div class="space-y-3">
                    @foreach($reportData['recentExpenses'] as $expense)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $expense['description'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $expense['category'] }} - {{ $expense['date'] }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-red-600 dark:text-red-400">
                                -NPR {{ number_format($expense['amount'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No expenses this month</p>
            @endif
        </div>

        {{-- Recent Income --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                Recent Income
            </h3>
            @if(count($reportData['recentIncomes']) > 0)
                <div class="space-y-3">
                    @foreach($reportData['recentIncomes'] as $income)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $income['source'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $income['date'] }}</p>
                                </div>
                            </div>
                            <span class="font-semibold text-green-600 dark:text-green-400">
                                +NPR {{ number_format($income['amount'], 2) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No income this month</p>
            @endif
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Helper to get colors based on theme
            function getChartColors() {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    textColor: isDark ? '#9CA3AF' : '#6B7280',
                    gridColor: isDark ? '#374151' : '#E5E7EB',
                    tooltipBg: isDark ? '#1F2937' : '#FFFFFF',
                    tooltipText: isDark ? '#F3F4F6' : '#111827'
                };
            }

            const colors = getChartColors();

            // Income vs Expense Chart
            const incomeCtx = document.getElementById('income-expense-chart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: ['Income', 'Expenses'],
                    datasets: [{
                        label: 'Amount',
                        data: [{{ $reportData['totalIncome'] }}, {{ $reportData['totalExpenses'] }}],
                        backgroundColor: ['#10B981', '#EF4444'],
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.55
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: colors.tooltipBg,
                            titleColor: colors.tooltipText,
                            bodyColor: colors.tooltipText,
                            borderColor: colors.gridColor,
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return 'NPR ' + context.raw.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: colors.gridColor
                            },
                            ticks: {
                                color: colors.textColor,
                                callback: function(value) {
                                    return 'NPR ' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: colors.textColor
                            }
                        }
                    }
                }
            });

            // Category Pie Chart
            const categoryData = @json($reportData['expensesByCategory']);
            const categoryLabels = categoryData.map(c => c.name);
            const categoryValues = categoryData.map(c => c.total);
            const categoryColors = categoryData.map(c => c.color || '#6366f1');
            
            const categoryCanvas = document.getElementById('category-chart');
            
            if (categoryValues.length > 0) {
                const categoryCtx = categoryCanvas.getContext('2d');
                new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryLabels,
                        datasets: [{
                            data: categoryValues,
                            backgroundColor: categoryColors.length > 0 ? categoryColors : ['#6366f1', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '60%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: colors.textColor,
                                    usePointStyle: true,
                                    padding: 20
                                }
                            },
                            tooltip: {
                                backgroundColor: colors.tooltipBg,
                                titleColor: colors.tooltipText,
                                bodyColor: colors.tooltipText,
                                borderColor: colors.gridColor,
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.raw;
                                        const total = context.chart._metasets[context.datasetIndex].total;
                                        const percentage = ((value / total) * 100).toFixed(1) + '%';
                                        return context.label + ': NPR ' + value.toLocaleString() + ' (' + percentage + ')';
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                categoryCanvas.parentElement.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
            }
        });
    </script>
    @endpush
</x-filament-panels::page>