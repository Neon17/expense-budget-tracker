<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    /**
     * Display a listing of budgets.
     */
    public function index(Request $request): JsonResponse
    {
        $budgets = $request->user()->budgets()
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($budget) {
                return [
                    'id' => $budget->id,
                    'monthly_limit' => (float) $budget->monthly_limit,
                    'month' => $budget->month,
                    'currency' => $budget->currency,
                    'spent' => (float) $budget->spent,
                    'remaining' => (float) $budget->remaining,
                    'usage_percentage' => $budget->usage_percentage,
                    'status' => $budget->status,
                ];
            });

        return response()->json([
            'data' => $budgets,
        ]);
    }

    /**
     * Get current month's budget.
     */
    public function current(Request $request): JsonResponse
    {
        $currentMonth = now()->format('Y-m');
        $budget = $request->user()->budgets()
            ->where('month', $currentMonth)
            ->first();

        if (!$budget) {
            return response()->json([
                'message' => 'No budget set for current month.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $budget->spent,
                'remaining' => (float) $budget->remaining,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ],
        ]);
    }

    /**
     * Store or update a budget for a specific month.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_limit' => ['required', 'numeric', 'min:1'],
            'month' => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');

        $budget = $request->user()->budgets()->updateOrCreate(
            ['month' => $month],
            [
                'monthly_limit' => $validated['monthly_limit'],
                'currency' => $validated['currency'] ?? $request->user()->currency ?? 'NPR',
            ]
        );

        return response()->json([
            'message' => 'Budget saved successfully',
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $budget->spent,
                'remaining' => (float) $budget->remaining,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ],
        ], 201);
    }

    /**
     * Update budget for current month.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'monthly_limit' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $currentMonth = now()->format('Y-m');

        $budget = $request->user()->budgets()->updateOrCreate(
            ['month' => $currentMonth],
            [
                'monthly_limit' => $validated['monthly_limit'],
                'currency' => $validated['currency'] ?? $request->user()->currency ?? 'NPR',
            ]
        );

        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $budget->spent,
                'remaining' => (float) $budget->remaining,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ],
        ]);
    }

    /**
     * Display the specified budget.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $budget = $request->user()->budgets()->find($id);

        if (!$budget) {
            return response()->json([
                'message' => 'Budget not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $budget->spent,
                'remaining' => (float) $budget->remaining,
                'usage_percentage' => $budget->usage_percentage,
                'status' => $budget->status,
            ],
        ]);
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $budget = $request->user()->budgets()->find($id);

        if (!$budget) {
            return response()->json([
                'message' => 'Budget not found.',
            ], 404);
        }

        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully',
        ]);
    }
}
