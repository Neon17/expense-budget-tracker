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
     * Budgets are owned by the data owner (parent for family).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        
        $budgets = $dataOwner->budgets()
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($budget) use ($user) {
                // Calculate spent from all family members
                $userIds = $user->getSharedDashboardUserIds();
                $spent = \App\Models\Expense::whereIn('user_id', $userIds)
                    ->month($budget->month)
                    ->sum('amount');
                    
                return [
                    'id' => $budget->id,
                    'monthly_limit' => (float) $budget->monthly_limit,
                    'month' => $budget->month,
                    'currency' => $budget->currency,
                    'spent' => (float) $spent,
                    'remaining' => (float) max(0, $budget->monthly_limit - $spent),
                    'usage_percentage' => $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0,
                    'status' => $this->getBudgetStatus($spent, $budget->monthly_limit),
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
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        $userIds = $user->getSharedDashboardUserIds();
        
        $currentMonth = now()->format('Y-m');
        $budget = $dataOwner->budgets()
            ->where('month', $currentMonth)
            ->first();

        if (!$budget) {
            return response()->json([
                'message' => 'No budget set for current month.',
                'data' => null,
            ]);
        }

        // Calculate spent from all family members
        $spent = \App\Models\Expense::whereIn('user_id', $userIds)
            ->month($currentMonth)
            ->sum('amount');

        return response()->json([
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $spent,
                'remaining' => (float) max(0, $budget->monthly_limit - $spent),
                'usage_percentage' => $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0,
                'status' => $this->getBudgetStatus($spent, $budget->monthly_limit),
            ],
        ]);
    }

    /**
     * Store or update a budget for a specific month.
     * Only the data owner can create/modify budgets.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        $userIds = $user->getSharedDashboardUserIds();
        
        $validated = $request->validate([
            'monthly_limit' => ['required', 'numeric', 'min:1'],
            'month' => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');

        $budget = $dataOwner->budgets()->updateOrCreate(
            ['month' => $month],
            [
                'monthly_limit' => $validated['monthly_limit'],
                'currency' => $validated['currency'] ?? $dataOwner->currency ?? 'NPR',
            ]
        );

        // Calculate spent from all family members
        $spent = \App\Models\Expense::whereIn('user_id', $userIds)
            ->month($month)
            ->sum('amount');

        return response()->json([
            'message' => 'Budget saved successfully',
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $spent,
                'remaining' => (float) max(0, $budget->monthly_limit - $spent),
                'usage_percentage' => $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0,
                'status' => $this->getBudgetStatus($spent, $budget->monthly_limit),
            ],
        ], 201);
    }

    /**
     * Update budget for current month.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        $userIds = $user->getSharedDashboardUserIds();
        
        $validated = $request->validate([
            'monthly_limit' => ['required', 'numeric', 'min:1'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        $currentMonth = now()->format('Y-m');

        $budget = $dataOwner->budgets()->updateOrCreate(
            ['month' => $currentMonth],
            [
                'monthly_limit' => $validated['monthly_limit'],
                'currency' => $validated['currency'] ?? $dataOwner->currency ?? 'NPR',
            ]
        );

        // Calculate spent from all family members
        $spent = \App\Models\Expense::whereIn('user_id', $userIds)
            ->month($currentMonth)
            ->sum('amount');

        return response()->json([
            'message' => 'Budget updated successfully',
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $spent,
                'remaining' => (float) max(0, $budget->monthly_limit - $spent),
                'usage_percentage' => $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0,
                'status' => $this->getBudgetStatus($spent, $budget->monthly_limit),
            ],
        ]);
    }

    /**
     * Display the specified budget.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        $userIds = $user->getSharedDashboardUserIds();
        
        $budget = $dataOwner->budgets()->find($id);

        if (!$budget) {
            return response()->json([
                'message' => 'Budget not found.',
            ], 404);
        }

        // Calculate spent from all family members
        $spent = \App\Models\Expense::whereIn('user_id', $userIds)
            ->month($budget->month)
            ->sum('amount');

        return response()->json([
            'data' => [
                'id' => $budget->id,
                'monthly_limit' => (float) $budget->monthly_limit,
                'month' => $budget->month,
                'currency' => $budget->currency,
                'spent_this_month' => (float) $spent,
                'remaining' => (float) max(0, $budget->monthly_limit - $spent),
                'usage_percentage' => $budget->monthly_limit > 0 ? round(($spent / $budget->monthly_limit) * 100, 2) : 0,
                'status' => $this->getBudgetStatus($spent, $budget->monthly_limit),
            ],
        ]);
    }

    /**
     * Remove the specified budget.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        
        $budget = $dataOwner->budgets()->find($id);

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

    /**
     * Get budget status based on spending.
     */
    private function getBudgetStatus(float $spent, float $limit): string
    {
        if ($limit <= 0) {
            return 'on_track';
        }
        
        $percentage = ($spent / $limit) * 100;
        
        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 90) {
            return 'critical';
        } elseif ($percentage >= 70) {
            return 'warning';
        }
        
        return 'on_track';
    }
}
