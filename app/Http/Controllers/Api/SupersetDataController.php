<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FamilyGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupersetDataController extends Controller
{
    /**
     * Get aggregated expense data for Superset analytics
     * 
     * @return JsonResponse
     */
    public function expenseAnalytics(): JsonResponse
    {
        $user = Auth::user();
        
        // Get user's family group if they have one
        $familyId = $user->family_group_id;
        
        $query = Expense::query();
        
        if ($familyId) {
            // Include family members' expenses
            $query->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        
        $expenses = $query
            ->select([
                'id',
                'user_id',
                'category_id',
                'amount',
                'description',
                'expense_date',
                'created_at',
            ])
            ->with(['category:id,name,color', 'user:id,name,email'])
            ->orderBy('expense_date', 'desc')
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'user_id' => $expense->user_id,
                    'user_name' => $expense->user?->name,
                    'category_id' => $expense->category_id,
                    'category_name' => $expense->category?->name,
                    'category_color' => $expense->category?->color,
                    'amount' => (float) $expense->amount,
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date->format('Y-m-d'),
                    'year' => $expense->expense_date->year,
                    'month' => $expense->expense_date->month,
                    'month_name' => $expense->expense_date->format('F'),
                    'day' => $expense->expense_date->day,
                    'day_of_week' => $expense->expense_date->format('l'),
                    'week_of_year' => $expense->expense_date->weekOfYear,
                    'created_at' => $expense->created_at->toIso8601String(),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $expenses,
            'meta' => [
                'total_count' => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get monthly aggregated data for Superset
     * 
     * @return JsonResponse
     */
    public function monthlyAggregates(): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_group_id;
        
        $query = Expense::query();
        
        if ($familyId) {
            $query->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        
        $monthlyData = $query
            ->select([
                DB::raw('YEAR(expense_date) as year'),
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('AVG(amount) as avg_amount'),
                DB::raw('MAX(amount) as max_amount'),
                DB::raw('MIN(amount) as min_amount'),
            ])
            ->groupBy(DB::raw('YEAR(expense_date)'), DB::raw('MONTH(expense_date)'))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $date = Carbon::createFromDate($item->year, $item->month, 1);
                return [
                    'year' => $item->year,
                    'month' => $item->month,
                    'month_name' => $date->format('F'),
                    'year_month' => $date->format('Y-m'),
                    'total_amount' => round((float) $item->total_amount, 2),
                    'transaction_count' => (int) $item->transaction_count,
                    'avg_amount' => round((float) $item->avg_amount, 2),
                    'max_amount' => round((float) $item->max_amount, 2),
                    'min_amount' => round((float) $item->min_amount, 2),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $monthlyData,
            'meta' => [
                'total_months' => $monthlyData->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get category breakdown data for Superset
     * 
     * @return JsonResponse
     */
    public function categoryBreakdown(): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_group_id;
        
        $query = Expense::query();
        
        if ($familyId) {
            $query->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        
        $categoryData = $query
            ->select([
                'category_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('AVG(amount) as avg_amount'),
            ])
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->get()
            ->map(function ($item) use ($query) {
                $totalAll = Expense::sum('amount') ?: 1;
                return [
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name ?? 'Uncategorized',
                    'category_color' => $item->category?->color ?? '#888888',
                    'total_amount' => round((float) $item->total_amount, 2),
                    'transaction_count' => (int) $item->transaction_count,
                    'avg_amount' => round((float) $item->avg_amount, 2),
                    'percentage' => round(($item->total_amount / $totalAll) * 100, 2),
                ];
            })
            ->sortByDesc('total_amount')
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $categoryData,
            'meta' => [
                'total_categories' => $categoryData->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get budget vs actual spending data for Superset
     * 
     * @return JsonResponse
     */
    public function budgetVsActual(): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_group_id;
        
        $budgetQuery = Budget::query();
        $expenseQuery = Expense::query();
        
        if ($familyId) {
            $budgetQuery->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
            $expenseQuery->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
        } else {
            $budgetQuery->where('user_id', $user->id);
            $expenseQuery->where('user_id', $user->id);
        }
        
        // Get budgets with their category spending
        $budgets = $budgetQuery
            ->with('category:id,name,color')
            ->get()
            ->map(function ($budget) use ($expenseQuery, $familyId, $user) {
                // Calculate actual spending for this budget's category
                $actualQuery = Expense::query();
                
                if ($familyId) {
                    $actualQuery->whereHas('user', function($q) use ($familyId) {
                        $q->where('family_group_id', $familyId);
                    });
                } else {
                    $actualQuery->where('user_id', $user->id);
                }
                
                $actual = $actualQuery
                    ->where('category_id', $budget->category_id)
                    ->whereBetween('expense_date', [$budget->start_date, $budget->end_date])
                    ->sum('amount');
                
                $variance = $budget->amount - $actual;
                $utilizationPercent = $budget->amount > 0 ? ($actual / $budget->amount) * 100 : 0;
                
                return [
                    'budget_id' => $budget->id,
                    'category_id' => $budget->category_id,
                    'category_name' => $budget->category?->name ?? 'Uncategorized',
                    'category_color' => $budget->category?->color ?? '#888888',
                    'budget_amount' => round((float) $budget->amount, 2),
                    'actual_amount' => round((float) $actual, 2),
                    'variance' => round($variance, 2),
                    'utilization_percent' => round($utilizationPercent, 2),
                    'status' => $utilizationPercent > 100 ? 'over_budget' : ($utilizationPercent > 80 ? 'warning' : 'on_track'),
                    'start_date' => $budget->start_date->format('Y-m-d'),
                    'end_date' => $budget->end_date->format('Y-m-d'),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $budgets,
            'meta' => [
                'total_budgets' => $budgets->count(),
                'over_budget_count' => $budgets->where('status', 'over_budget')->count(),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get daily trend data for Superset
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function dailyTrend(Request $request): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_group_id;
        $days = $request->get('days', 30);
        
        $query = Expense::query();
        
        if ($familyId) {
            $query->whereHas('user', function($q) use ($familyId) {
                $q->where('family_group_id', $familyId);
            });
        } else {
            $query->where('user_id', $user->id);
        }
        
        $startDate = now()->subDays($days);
        
        $dailyData = $query
            ->where('expense_date', '>=', $startDate)
            ->select([
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as transaction_count'),
            ])
            ->groupBy(DB::raw('DATE(expense_date)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $date = Carbon::parse($item->date);
                return [
                    'date' => $item->date,
                    'day_of_week' => $date->format('l'),
                    'day_short' => $date->format('D'),
                    'total_amount' => round((float) $item->total_amount, 2),
                    'transaction_count' => (int) $item->transaction_count,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $dailyData,
            'meta' => [
                'days_requested' => $days,
                'actual_days' => $dailyData->count(),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get family member comparison data for Superset
     * 
     * @return JsonResponse
     */
    public function familyComparison(): JsonResponse
    {
        $user = Auth::user();
        $familyId = $user->family_group_id;
        
        if (!$familyId) {
            return response()->json([
                'success' => false,
                'message' => 'User is not part of a family group',
                'data' => [],
            ], 400);
        }
        
        $memberData = DB::table('users')
            ->where('family_group_id', $familyId)
            ->leftJoin('expenses', 'users.id', '=', 'expenses.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.account_type',
                DB::raw('COALESCE(SUM(expenses.amount), 0) as total_spent'),
                DB::raw('COUNT(expenses.id) as transaction_count'),
                DB::raw('COALESCE(AVG(expenses.amount), 0) as avg_transaction'),
            ])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.account_type')
            ->get()
            ->map(function ($member) {
                return [
                    'user_id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'account_type' => $member->account_type,
                    'total_spent' => round((float) $member->total_spent, 2),
                    'transaction_count' => (int) $member->transaction_count,
                    'avg_transaction' => round((float) $member->avg_transaction, 2),
                ];
            });
        
        $totalFamilySpent = $memberData->sum('total_spent') ?: 1;
        
        $memberData = $memberData->map(function ($member) use ($totalFamilySpent) {
            $member['percentage_of_total'] = round(($member['total_spent'] / $totalFamilySpent) * 100, 2);
            return $member;
        });
        
        return response()->json([
            'success' => true,
            'data' => $memberData,
            'meta' => [
                'family_id' => $familyId,
                'member_count' => $memberData->count(),
                'total_family_spent' => round($totalFamilySpent, 2),
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Export all data in a format suitable for Superset import
     * 
     * @return JsonResponse
     */
    public function exportForSuperset(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'expenses' => $this->expenseAnalytics()->getData()->data,
                'monthly' => $this->monthlyAggregates()->getData()->data,
                'categories' => $this->categoryBreakdown()->getData()->data,
                'budgets' => $this->budgetVsActual()->getData()->data,
                'daily_trend' => $this->dailyTrend(new Request(['days' => 90]))->getData()->data,
            ],
            'meta' => [
                'export_version' => '1.0',
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
