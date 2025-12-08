<x-filament-panels::page>
    {{-- ApexCharts CDN --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @endpush

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
                <x-heroicon-o-calendar class="w-5 h-5" />
                <span>{{ $reportData['expenseCount'] + $reportData['incomeCount'] }} Transactions</span>
            </div>
        </div>
    </div>

    {{-- Main Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Expenses --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-danger-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-danger-100 dark:bg-danger-900/30 rounded-lg">
                    <x-heroicon-o-arrow-trending-down class="w-6 h-6 text-danger-600 dark:text-danger-400" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                NPR {{ number_format($reportData['totalExpenses'], 2) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="text-gray-500">{{ $reportData['expenseCount'] }} transactions</span>
            </div>
        </div>

        {{-- Total Income --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-success-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-success-100 dark:bg-success-900/30 rounded-lg">
                    <x-heroicon-o-arrow-trending-up class="w-6 h-6 text-success-600 dark:text-success-400" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Income</span>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                NPR {{ number_format($reportData['totalIncome'], 2) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="text-gray-500">{{ $reportData['incomeCount'] }} transactions</span>
            </div>
        </div>

        {{-- Net Savings --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 {{ $reportData['savings'] >= 0 ? 'bg-success-500/10' : 'bg-danger-500/10' }} rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 {{ $reportData['savings'] >= 0 ? 'bg-success-100 dark:bg-success-900/30' : 'bg-danger-100 dark:bg-danger-900/30' }} rounded-lg">
                    <x-heroicon-o-banknotes class="w-6 h-6 {{ $reportData['savings'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Savings</span>
            </div>
            <div class="text-2xl font-bold {{ $reportData['savings'] >= 0 ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                NPR {{ number_format($reportData['savings'], 2) }}
            </div>
            <div class="mt-2 flex items-center gap-2 text-sm">
                <span class="{{ $reportData['savingsRate'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">{{ $reportData['savingsRate'] }}% savings rate</span>
            </div>
        </div>

        {{-- Budget Status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 relative overflow-hidden">
            @php
                $budgetColor = 'primary';
                if ($reportData['budget']) {
                    if ($reportData['budgetUsage'] >= 100) $budgetColor = 'danger';
                    elseif ($reportData['budgetUsage'] >= 70) $budgetColor = 'warning';
                    else $budgetColor = 'success';
                }
            @endphp
            <div class="absolute top-0 right-0 w-20 h-20 bg-{{ $budgetColor }}-500/10 rounded-bl-full"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="p-2 bg-{{ $budgetColor }}-100 dark:bg-{{ $budgetColor }}-900/30 rounded-lg">
                    <x-heroicon-o-chart-pie class="w-6 h-6 text-{{ $budgetColor }}-600 dark:text-{{ $budgetColor }}-400" />
                </div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Budget Status</span>
            </div>
            @if($reportData['budget'])
                <div class="text-2xl font-bold text-{{ $budgetColor }}-600 dark:text-{{ $budgetColor }}-400">
                    {{ number_format($reportData['budgetUsage'], 1) }}%
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full bg-{{ $budgetColor }}-500 transition-all duration-500" style="width: {{ min($reportData['budgetUsage'], 100) }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500 mt-1">NPR {{ number_format($reportData['budget']->remaining, 2) }} remaining</span>
                </div>
            @else
                <div class="text-2xl font-bold text-gray-400">Not Set</div>
                <div class="mt-2 text-sm text-gray-500">Set a monthly budget</div>
            @endif
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Daily Spending Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-primary-500" />
                Daily Spending Pattern
            </h3>
            <div id="dailySpendingChart" class="h-64"></div>
        </div>

        {{-- Category Breakdown Donut --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-chart-pie class="w-5 h-5 text-primary-500" />
                Category Breakdown
            </h3>
            <div id="categoryDonutChart" class="h-64"></div>
        </div>
    </div>

    {{-- Category Details --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <x-heroicon-o-tag class="w-5 h-5 text-primary-500" />
            Expense Categories
        </h3>
        
        @if(count($reportData['categoryBreakdown']) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($reportData['categoryBreakdown'] as $category)
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: {{ $category['color'] }}20;">
                                <div class="w-4 h-4 rounded-full" style="background-color: {{ $category['color'] }};"></div>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $category['name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $category['count'] }} transactions</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $category['percentage'] }}%</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-2">
                            <div class="h-2 rounded-full transition-all duration-500" style="width: {{ $category['percentage'] }}%; background-color: {{ $category['color'] }};"></div>
                        </div>
                        <div class="text-sm font-semibold" style="color: {{ $category['color'] }};">
                            NPR {{ number_format($category['total'], 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-gray-500">No expenses recorded for this month.</p>
            </div>
        @endif
    </div>

    {{-- Top Expenses & Quick Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Top Expenses --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-fire class="w-5 h-5 text-danger-500" />
                Top 5 Expenses
            </h3>
            
            @if($reportData['topExpenses']->count() > 0)
                <div class="space-y-3">
                    @foreach($reportData['topExpenses'] as $index => $expense)
                        <div class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center text-danger-600 dark:text-danger-400 font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $expense->category?->color ?? '#6366F1' }};"></div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $expense->category?->name ?? 'Uncategorized' }}</div>
                                <div class="text-xs text-gray-500">{{ $expense->date->format('M d, Y') }} • {{ Str::limit($expense->note, 30) ?? 'No note' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-danger-600 dark:text-danger-400">NPR {{ number_format($expense->amount, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-currency-dollar class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                    <p class="text-gray-500">No expenses recorded.</p>
                </div>
            @endif
        </div>

        {{-- Quick Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-sparkles class="w-5 h-5 text-warning-500" />
                Quick Stats
            </h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-calculator class="w-5 h-5 text-primary-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Avg. Daily Expense</span>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">NPR {{ number_format($reportData['avgDailyExpense'], 2) }}</span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-success-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Days with Expenses</span>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ count($reportData['dailySpending']) }}</span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-tag class="w-5 h-5 text-warning-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Categories Used</span>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ count($reportData['categoryBreakdown']) }}</span>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-document-text class="w-5 h-5 text-danger-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total Transactions</span>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $reportData['expenseCount'] + $reportData['incomeCount'] }}</span>
                </div>

                @if($reportData['budget'])
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-banknotes class="w-5 h-5 text-primary-500" />
                        <span class="text-sm text-gray-600 dark:text-gray-400">Monthly Budget</span>
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">NPR {{ number_format($reportData['budget']->monthly_limit, 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ApexCharts Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Daily Spending Chart
            const dailyData = @json($reportData['dailySpending']);
            const dailyLabels = Object.keys(dailyData).map(date => {
                const d = new Date(date);
                return d.getDate();
            });
            const dailyValues = Object.values(dailyData);

            const dailyOptions = {
                series: [{
                    name: 'Spending',
                    data: dailyValues
                }],
                chart: {
                    type: 'area',
                    height: 256,
                    toolbar: { show: false },
                    background: 'transparent'
                },
                colors: ['#EF4444'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: dailyLabels,
                    labels: { style: { colors: '#9CA3AF' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9CA3AF' },
                        formatter: (val) => 'NPR ' + val.toLocaleString()
                    }
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: (val) => 'NPR ' + val.toLocaleString() }
                }
            };

            if (dailyValues.length > 0) {
                new ApexCharts(document.querySelector("#dailySpendingChart"), dailyOptions).render();
            }

            // Category Donut Chart
            const categoryData = @json($reportData['categoryBreakdown']);
            const categoryLabels = categoryData.map(c => c.name);
            const categoryValues = categoryData.map(c => parseFloat(c.total));
            const categoryColors = categoryData.map(c => c.color);

            const donutOptions = {
                series: categoryValues,
                chart: {
                    type: 'donut',
                    height: 256,
                    background: 'transparent'
                },
                labels: categoryLabels,
                colors: categoryColors,
                legend: {
                    position: 'bottom',
                    labels: { colors: '#9CA3AF' }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    color: '#9CA3AF',
                                    formatter: function(w) {
                                        return 'NPR ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: (val) => 'NPR ' + val.toLocaleString() }
                }
            };

            if (categoryValues.length > 0) {
                new ApexCharts(document.querySelector("#categoryDonutChart"), donutOptions).render();
            }
        });
    </script>
</x-filament-panels::page>
