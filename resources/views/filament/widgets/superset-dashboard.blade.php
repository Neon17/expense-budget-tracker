<x-filament-widgets::widget>
    <div x-data="supersetDashboard()" x-init="initCharts()" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Analytics Dashboard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Comprehensive financial overview powered by advanced charts</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 12H9v-2h2v2zm0-4H9V6h2v4z"/>
                    </svg>
                    Live Data
                </span>
            </div>
        </div>

        <!-- Main Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Trend Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Trend</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last 12 months</span>
                </div>
                <div id="monthly-trend-chart" class="h-72"></div>
            </div>

            <!-- Category Breakdown -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Expense Categories</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">This month</span>
                </div>
                <div id="category-chart" class="h-72"></div>
            </div>

            <!-- Daily Spending Trend -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daily Spending</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last 30 days</span>
                </div>
                <div id="daily-trend-chart" class="h-72"></div>
            </div>

            <!-- Income vs Expense -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Income vs Expense</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last 6 months</span>
                </div>
                <div id="income-expense-chart" class="h-72"></div>
            </div>
        </div>

        <!-- Bottom Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Savings Rate Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Savings Rate</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last 6 months</span>
                </div>
                <div id="savings-rate-chart" class="h-64"></div>
            </div>

            <!-- Weekly Spending Pattern -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Weekly Pattern</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Last 30 days</span>
                </div>
                <div id="weekly-pattern-chart" class="h-64"></div>
            </div>
        </div>

        <!-- API Endpoints Info -->
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Superset API Endpoints</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Use these endpoints for Apache Superset or external BI tools integration:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                    <code class="text-xs text-primary-600 dark:text-primary-400 font-mono">/api/analytics/superset/expenses</code>
                    <p class="text-xs text-gray-500 mt-1">Flat expense dataset</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                    <code class="text-xs text-primary-600 dark:text-primary-400 font-mono">/api/analytics/superset/incomes</code>
                    <p class="text-xs text-gray-500 mt-1">Flat income dataset</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                    <code class="text-xs text-primary-600 dark:text-primary-400 font-mono">/api/analytics/superset/monthly-aggregate</code>
                    <p class="text-xs text-gray-500 mt-1">Monthly aggregates</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        function supersetDashboard() {
            return {
                monthlyData: @json($monthlyData),
                categoryData: @json($categoryData),
                dailyTrend: @json($dailyTrend),
                incomeVsExpense: @json($incomeVsExpense),
                savingsRate: @json($savingsRate),
                weeklyPattern: @json($weeklyPattern),
                currency: @json($currency),
                charts: {},

                initCharts() {
                    this.$nextTick(() => {
                        this.renderMonthlyTrendChart();
                        this.renderCategoryChart();
                        this.renderDailyTrendChart();
                        this.renderIncomeExpenseChart();
                        this.renderSavingsRateChart();
                        this.renderWeeklyPatternChart();
                    });
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('en-NP', {
                        style: 'currency',
                        currency: this.currency,
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount);
                },

                getChartTheme() {
                    return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                },

                renderMonthlyTrendChart() {
                    const options = {
                        series: [{
                            name: 'Income',
                            data: this.monthlyData.map(d => d.income)
                        }, {
                            name: 'Expenses',
                            data: this.monthlyData.map(d => d.expenses)
                        }, {
                            name: 'Savings',
                            data: this.monthlyData.map(d => d.savings)
                        }],
                        chart: {
                            type: 'area',
                            height: 280,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#10B981', '#EF4444', '#6366F1'],
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.1,
                            }
                        },
                        xaxis: {
                            categories: this.monthlyData.map(d => d.month),
                            labels: { style: { colors: '#9CA3AF' } }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9CA3AF' },
                                formatter: (val) => this.formatCurrency(val)
                            }
                        },
                        legend: { position: 'top' },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => this.formatCurrency(val) }
                        }
                    };

                    if (this.charts.monthly) this.charts.monthly.destroy();
                    this.charts.monthly = new ApexCharts(document.querySelector("#monthly-trend-chart"), options);
                    this.charts.monthly.render();
                },

                renderCategoryChart() {
                    if (this.categoryData.length === 0) {
                        document.querySelector("#category-chart").innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No expense data available</div>';
                        return;
                    }

                    const options = {
                        series: this.categoryData.map(d => d.value),
                        labels: this.categoryData.map(d => d.name),
                        colors: this.categoryData.map(d => d.color),
                        chart: {
                            type: 'donut',
                            height: 280,
                            background: 'transparent'
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
                                            formatter: () => this.formatCurrency(this.categoryData.reduce((a, b) => a + b.value, 0))
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: { colors: '#9CA3AF' }
                        },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => this.formatCurrency(val) }
                        }
                    };

                    if (this.charts.category) this.charts.category.destroy();
                    this.charts.category = new ApexCharts(document.querySelector("#category-chart"), options);
                    this.charts.category.render();
                },

                renderDailyTrendChart() {
                    const options = {
                        series: [{
                            name: 'Spending',
                            data: this.dailyTrend.map(d => d.amount)
                        }],
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#6366F1'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                columnWidth: '60%',
                            }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: this.dailyTrend.map(d => d.date),
                            labels: {
                                style: { colors: '#9CA3AF' },
                                rotate: -45,
                                rotateAlways: true
                            }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9CA3AF' },
                                formatter: (val) => this.formatCurrency(val)
                            }
                        },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => this.formatCurrency(val) }
                        }
                    };

                    if (this.charts.daily) this.charts.daily.destroy();
                    this.charts.daily = new ApexCharts(document.querySelector("#daily-trend-chart"), options);
                    this.charts.daily.render();
                },

                renderIncomeExpenseChart() {
                    const options = {
                        series: [{
                            name: 'Income',
                            data: this.incomeVsExpense.map(d => d.income)
                        }, {
                            name: 'Expense',
                            data: this.incomeVsExpense.map(d => d.expense)
                        }],
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#10B981', '#EF4444'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                borderRadius: 6,
                                columnWidth: '55%',
                            }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: this.incomeVsExpense.map(d => d.month),
                            labels: { style: { colors: '#9CA3AF' } }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9CA3AF' },
                                formatter: (val) => this.formatCurrency(val)
                            }
                        },
                        legend: { position: 'top' },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => this.formatCurrency(val) }
                        }
                    };

                    if (this.charts.incomeExpense) this.charts.incomeExpense.destroy();
                    this.charts.incomeExpense = new ApexCharts(document.querySelector("#income-expense-chart"), options);
                    this.charts.incomeExpense.render();
                },

                renderSavingsRateChart() {
                    const options = {
                        series: [{
                            name: 'Savings Rate',
                            data: this.savingsRate.map(d => d.rate)
                        }],
                        chart: {
                            type: 'line',
                            height: 240,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#8B5CF6'],
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        markers: {
                            size: 6,
                            colors: ['#8B5CF6'],
                            strokeColors: '#fff',
                            strokeWidth: 2
                        },
                        xaxis: {
                            categories: this.savingsRate.map(d => d.month),
                            labels: { style: { colors: '#9CA3AF' } }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9CA3AF' },
                                formatter: (val) => val + '%'
                            },
                            min: -50,
                            max: 100
                        },
                        annotations: {
                            yaxis: [{
                                y: 20,
                                borderColor: '#10B981',
                                label: {
                                    text: 'Goal: 20%',
                                    style: { color: '#10B981', background: 'transparent' }
                                }
                            }]
                        },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => val + '%' }
                        }
                    };

                    if (this.charts.savingsRate) this.charts.savingsRate.destroy();
                    this.charts.savingsRate = new ApexCharts(document.querySelector("#savings-rate-chart"), options);
                    this.charts.savingsRate.render();
                },

                renderWeeklyPatternChart() {
                    const options = {
                        series: [{
                            name: 'Spending',
                            data: this.weeklyPattern.map(d => d.total)
                        }],
                        chart: {
                            type: 'radar',
                            height: 240,
                            toolbar: { show: false },
                            background: 'transparent'
                        },
                        colors: ['#F59E0B'],
                        xaxis: {
                            categories: this.weeklyPattern.map(d => d.day),
                            labels: { style: { colors: '#9CA3AF' } }
                        },
                        yaxis: { show: false },
                        stroke: { width: 2 },
                        fill: { opacity: 0.3 },
                        markers: { size: 4 },
                        theme: { mode: this.getChartTheme() },
                        tooltip: {
                            y: { formatter: (val) => this.formatCurrency(val) }
                        }
                    };

                    if (this.charts.weeklyPattern) this.charts.weeklyPattern.destroy();
                    this.charts.weeklyPattern = new ApexCharts(document.querySelector("#weekly-pattern-chart"), options);
                    this.charts.weeklyPattern.render();
                }
            }
        }
    </script>
</x-filament-widgets::widget>
