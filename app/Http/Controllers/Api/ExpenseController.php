<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->expenses()->with('category');

        // Filter by month (format: YYYY-MM)
        if ($request->has('month')) {
            $query->month($request->month);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search in notes
        if ($request->has('search')) {
            $query->where('note', 'like', '%' . $request->search . '%');
        }

        $expenses = $query->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $expenses->items(),
            'meta' => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'max:10'],
        ]);

        // Verify the category belongs to the user
        $category = $request->user()->categories()->find($validated['category_id']);
        if (!$category) {
            return response()->json([
                'message' => 'Category not found or does not belong to you.',
            ], 404);
        }

        $expense = $request->user()->expenses()->create([
            'amount' => $validated['amount'],
            'category_id' => $validated['category_id'],
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
            'currency' => $validated['currency'] ?? $request->user()->currency ?? 'NPR',
        ]);

        $expense->load('category');

        return response()->json([
            'message' => 'Expense created successfully',
            'data' => $expense,
        ], 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $expense = $request->user()->expenses()->with('category')->find($id);

        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found.',
            ], 404);
        }

        return response()->json([
            'data' => $expense,
        ]);
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $expense = $request->user()->expenses()->find($id);

        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found.',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'date' => ['sometimes', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Verify the category belongs to the user if provided
        if (isset($validated['category_id'])) {
            $category = $request->user()->categories()->find($validated['category_id']);
            if (!$category) {
                return response()->json([
                    'message' => 'Category not found or does not belong to you.',
                ], 404);
            }
        }

        $expense->update($validated);
        $expense->load('category');

        return response()->json([
            'message' => 'Expense updated successfully',
            'data' => $expense,
        ]);
    }

    /**
     * Remove the specified expense.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $expense = $request->user()->expenses()->find($id);

        if (!$expense) {
            return response()->json([
                'message' => 'Expense not found.',
            ], 404);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Get expense summary for current month.
     */
    public function summary(Request $request): JsonResponse
    {
        $month = $request->month ?? now()->format('Y-m');

        $total = $request->user()->expenses()
            ->month($month)
            ->sum('amount');

        $byCategory = $request->user()->expenses()
            ->with('category')
            ->month($month)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'month' => $month,
            'total' => (float) $total,
            'by_category' => $byCategory,
        ]);
    }
}
