<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\FamilyGroupController;
use App\Http\Controllers\Api\FamilyUserController;
use App\Http\Controllers\Api\SupersetDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::get('/expenses-summary', [ExpenseController::class, 'summary']);

    // Incomes
    Route::apiResource('incomes', IncomeController::class);
    Route::get('/incomes-summary', [IncomeController::class, 'summary']);

    // Categories
    Route::apiResource('categories', CategoryController::class);

    // Budgets
    Route::get('/budget/current', [BudgetController::class, 'current']);
    Route::put('/budget', [BudgetController::class, 'update']);
    Route::apiResource('budgets', BudgetController::class);

    // Analytics
    Route::prefix('analytics')->group(function () {
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
        Route::get('/monthly-trend', [AnalyticsController::class, 'monthlyTrend']);
        Route::get('/category-breakdown', [AnalyticsController::class, 'categoryBreakdown']);
        Route::get('/budget-vs-actual', [AnalyticsController::class, 'budgetVsActual']);
        Route::get('/income-vs-expense', [AnalyticsController::class, 'incomeVsExpense']);
        
        // New analytics endpoints
        Route::get('/weekly-stats', [AnalyticsController::class, 'weeklyStats']);
        Route::get('/category-stats', [AnalyticsController::class, 'categoryStats']);
        Route::get('/savings-rate', [AnalyticsController::class, 'savingsRate']);
        
        // Superset integration APIs
        Route::prefix('superset')->group(function () {
            Route::get('/expenses', [AnalyticsController::class, 'supersetExpenseData']);
            Route::get('/incomes', [AnalyticsController::class, 'supersetIncomeData']);
            Route::get('/monthly-aggregate', [AnalyticsController::class, 'supersetMonthlyAggregate']);
        });
    });

    // Family Groups
    Route::prefix('family-groups')->group(function () {
        Route::get('/', [FamilyGroupController::class, 'index']);
        Route::post('/', [FamilyGroupController::class, 'store']);
        Route::post('/join', [FamilyGroupController::class, 'join']);
        Route::get('/{familyGroup}', [FamilyGroupController::class, 'show']);
        Route::put('/{familyGroup}', [FamilyGroupController::class, 'update']);
        Route::delete('/{familyGroup}', [FamilyGroupController::class, 'destroy']);
        Route::post('/{familyGroup}/leave', [FamilyGroupController::class, 'leave']);
        Route::post('/{familyGroup}/regenerate-code', [FamilyGroupController::class, 'regenerateInviteCode']);
        Route::post('/{familyGroup}/transfer-ownership', [FamilyGroupController::class, 'transferOwnership']);
        Route::get('/{familyGroup}/statistics', [FamilyGroupController::class, 'statistics']);
        Route::delete('/{familyGroup}/members/{member}', [FamilyGroupController::class, 'removeMember']);
        Route::put('/{familyGroup}/members/{member}/role', [FamilyGroupController::class, 'updateMemberRole']);
    });

    // Family Users (Parent-Child Accounts)
    Route::prefix('family')->group(function () {
        Route::get('/permissions', [FamilyUserController::class, 'availablePermissions']);
        Route::put('/update-family', [FamilyUserController::class, 'updateFamily']);
        Route::get('/statistics', [FamilyUserController::class, 'statistics']);
        Route::get('/children', [FamilyUserController::class, 'index']);
        Route::post('/children', [FamilyUserController::class, 'store']);
        Route::get('/children/{child}', [FamilyUserController::class, 'show']);
        Route::put('/children/{child}', [FamilyUserController::class, 'update']);
        Route::delete('/children/{child}', [FamilyUserController::class, 'destroy']);
        Route::delete('/children/{child}/force', [FamilyUserController::class, 'forceDestroy']);
        Route::post('/children/{child}/reactivate', [FamilyUserController::class, 'reactivate']);
        Route::put('/children/{child}/permissions', [FamilyUserController::class, 'updatePermissions']);
    });

    // Superset Data Sync API
    Route::prefix('superset')->group(function () {
        Route::get('/expenses', [SupersetDataController::class, 'expenseAnalytics']);
        Route::get('/monthly', [SupersetDataController::class, 'monthlyAggregates']);
        Route::get('/categories', [SupersetDataController::class, 'categoryBreakdown']);
        Route::get('/budgets', [SupersetDataController::class, 'budgetVsActual']);
        Route::get('/daily-trend', [SupersetDataController::class, 'dailyTrend']);
        Route::get('/family-comparison', [SupersetDataController::class, 'familyComparison']);
        Route::get('/export', [SupersetDataController::class, 'exportForSuperset']);
    });
});
