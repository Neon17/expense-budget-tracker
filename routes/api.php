<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\AnalyticsController;
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
    });
});
