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
}
