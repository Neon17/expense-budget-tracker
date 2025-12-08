<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    /**
     * Display a listing of incomes.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->incomes()->with('category');

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

        // Search in source or notes
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('source', 'like', '%' . $request->search . '%')
                  ->orWhere('note', 'like', '%' . $request->search . '%');
            });
        }

        $incomes = $query->orderBy('date', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => $incomes->items(),
            'meta' => [
                'current_page' => $incomes->currentPage(),
                'last_page' => $incomes->lastPage(),
                'per_page' => $incomes->perPage(),
                'total' => $incomes->total(),
            ],
        ]);
    }

    /**
     * Store a newly created income.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'source' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'currency' => ['nullable', 'string', 'max:10'],
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

        $income = $request->user()->incomes()->create([
            'amount' => $validated['amount'],
            'source' => $validated['source'],
            'category_id' => $validated['category_id'] ?? null,
            'date' => $validated['date'],
            'note' => $validated['note'] ?? null,
            'currency' => $validated['currency'] ?? $request->user()->currency ?? 'NPR',
        ]);

        $income->load('category');

        return response()->json([
            'message' => 'Income created successfully',
            'data' => $income,
        ], 201);
    }

    /**
     * Display the specified income.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $income = $request->user()->incomes()->with('category')->find($id);

        if (!$income) {
            return response()->json([
                'message' => 'Income not found.',
            ], 404);
        }

        return response()->json([
            'data' => $income,
        ]);
    }

    /**
     * Update the specified income.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $income = $request->user()->incomes()->find($id);

        if (!$income) {
            return response()->json([
                'message' => 'Income not found.',
            ], 404);
        }

        $validated = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'source' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
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

        $income->update($validated);
        $income->load('category');

        return response()->json([
            'message' => 'Income updated successfully',
            'data' => $income,
        ]);
    }

    /**
     * Remove the specified income.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $income = $request->user()->incomes()->find($id);

        if (!$income) {
            return response()->json([
                'message' => 'Income not found.',
            ], 404);
        }

        $income->delete();

        return response()->json([
            'message' => 'Income deleted successfully',
        ]);
    }

    /**
     * Get income summary for current month.
     */
    public function summary(Request $request): JsonResponse
    {
        $month = $request->month ?? now()->format('Y-m');

        $total = $request->user()->incomes()
            ->month($month)
            ->sum('amount');

        $bySource = $request->user()->incomes()
            ->month($month)
            ->selectRaw('source, SUM(amount) as total')
            ->groupBy('source')
            ->get()
            ->map(function ($item) {
                return [
                    'source' => $item->source,
                    'total' => (float) $item->total,
                ];
            });

        return response()->json([
            'month' => $month,
            'total' => (float) $total,
            'by_source' => $bySource,
        ]);
    }
}
