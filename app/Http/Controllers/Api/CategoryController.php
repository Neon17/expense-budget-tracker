<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->categories();

        // Filter by type
        if ($request->has('type')) {
            if ($request->type === 'expense') {
                $query->expenseType();
            } elseif ($request->type === 'income') {
                $query->incomeType();
            } else {
                $query->where('type', $request->type);
            }
        }

        $categories = $query->withCount(['expenses', 'incomes'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'in:expense,income,both'],
        ]);

        $category = $request->user()->categories()->create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $category = $request->user()->categories()
            ->withCount(['expenses', 'incomes'])
            ->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = $request->user()->categories()->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:100'],
            'type' => ['sometimes', 'in:expense,income,both'],
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $category = $request->user()->categories()->find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        // Check if category has expenses or incomes
        if ($category->expenses()->count() > 0 || $category->incomes()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated expenses or incomes.',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
