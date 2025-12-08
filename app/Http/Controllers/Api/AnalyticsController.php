<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    /**
     * Get monthly expense/income trend.
     */
    public function monthlyTrend(Request $request): JsonResponse
    {
        $year = $request->year ?? now()->year;

        $data = $this->analyticsService->getMonthlyTrend(
            $request->user(),
            (int) $year
        );

        return response()->json([
            'year' => $year,
            'data' => $data,
        ]);
    }

    /**
     * Get expense breakdown by category.
     */
    public function categoryBreakdown(Request $request): JsonResponse
    {
        $month = $request->month ?? now()->format('Y-m');

        $data = $this->analyticsService->getCategoryBreakdown(
            $request->user(),
            $month
        );

        return response()->json([
            'month' => $month,
            'data' => $data,
        ]);
    }

    /**
     * Get budget vs actual comparison.
     */
    public function budgetVsActual(Request $request): JsonResponse
    {
        $year = $request->year ?? now()->year;

        $data = $this->analyticsService->getBudgetVsActual(
            $request->user(),
            (int) $year
        );

        return response()->json([
            'year' => $year,
            'data' => $data,
        ]);
    }

    /**
     * Get dashboard summary.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->analyticsService->getDashboardSummary($request->user());

        return response()->json($data);
    }

    /**
     * Get income vs expense comparison.
     */
    public function incomeVsExpense(Request $request): JsonResponse
    {
        $year = $request->year ?? now()->year;

        $data = $this->analyticsService->getIncomeVsExpense(
            $request->user(),
            (int) $year
        );

        return response()->json([
            'year' => $year,
            'data' => $data,
        ]);
    }

    /**
     * Get weekly stats for current month.
     */
    public function weeklyStats(Request $request): JsonResponse
    {
        $month = $request->month ?? now()->format('Y-m');

        $data = $this->analyticsService->getWeeklyStats(
            $request->user(),
            $month
        );

        return response()->json([
            'month' => $month,
            'data' => $data,
        ]);
    }

    /**
     * Get category stats with detailed breakdown.
     */
    public function categoryStats(Request $request): JsonResponse
    {
        $type = $request->type ?? 'expense'; // expense or income
        $period = $request->period ?? 'month'; // month, quarter, year

        $data = $this->analyticsService->getCategoryStats(
            $request->user(),
            $type,
            $period
        );

        return response()->json([
            'type' => $type,
            'period' => $period,
            'data' => $data,
        ]);
    }

    /**
     * Get Superset-compatible dataset for expenses.
     */
    public function supersetExpenses(Request $request): JsonResponse
    {
        $startDate = $request->start_date ?? now()->subMonths(12)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $data = $this->analyticsService->getSupersetExpenseData(
            $request->user(),
            $startDate,
            $endDate
        );

        return response()->json([
            'columns' => ['date', 'amount', 'category', 'category_color', 'note', 'month', 'year', 'day_of_week'],
            'data' => $data,
        ]);
    }

    /**
     * Get Superset-compatible dataset for income.
     */
    public function supersetIncome(Request $request): JsonResponse
    {
        $startDate = $request->start_date ?? now()->subMonths(12)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        $data = $this->analyticsService->getSupersetIncomeData(
            $request->user(),
            $startDate,
            $endDate
        );

        return response()->json([
            'columns' => ['date', 'amount', 'source', 'category', 'category_color', 'note', 'month', 'year', 'day_of_week'],
            'data' => $data,
        ]);
    }

    /**
     * Get Superset-compatible aggregated monthly data.
     */
    public function supersetMonthlyAggregate(Request $request): JsonResponse
    {
        $year = $request->year ?? now()->year;

        $data = $this->analyticsService->getSupersetMonthlyAggregate(
            $request->user(),
            (int) $year
        );

        return response()->json([
            'columns' => ['month', 'month_name', 'total_expenses', 'total_income', 'net_savings', 'budget', 'budget_usage_pct'],
            'data' => $data,
        ]);
    }

    /**
     * Get savings rate over time.
     */
    public function savingsRate(Request $request): JsonResponse
    {
        $months = $request->months ?? 12;

        $data = $this->analyticsService->getSavingsRate(
            $request->user(),
            (int) $months
        );

        return response()->json([
            'months' => $months,
            'data' => $data,
        ]);
    }
}
