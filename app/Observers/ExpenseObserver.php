<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\Budget;
use App\Notifications\BudgetAlertNotification;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        $this->checkBudgetAlert($expense);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        $this->checkBudgetAlert($expense);
    }

    /**
     * Handle the Expense "deleted" event.
     */
    public function deleted(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "restored" event.
     */
    public function restored(Expense $expense): void
    {
        //
    }

    /**
     * Handle the Expense "force deleted" event.
     */
    public function forceDeleted(Expense $expense): void
    {
        //
    }

    /**
     * Check budget and send alert if threshold is crossed.
     */
    protected function checkBudgetAlert(Expense $expense): void
    {
        $user = $expense->user;
        $month = $expense->date->format('Y-m');
        
        $budget = Budget::where('user_id', $user->id)
            ->where('month', $month)
            ->first();

        if (!$budget) {
            return;
        }

        $percentage = $budget->usage_percentage;

        // Check thresholds and send notification if crossed
        $alertLevel = null;

        if ($percentage >= 100) {
            $alertLevel = 'exceeded';
        } elseif ($percentage >= 90) {
            $alertLevel = 'critical';
        } elseif ($percentage >= 70) {
            $alertLevel = 'warning';
        }

        if ($alertLevel) {
            // Check if we already sent this alert level for this month
            $existingAlert = $user->notifications()
                ->whereJsonContains('data->month', $month)
                ->whereJsonContains('data->alert_level', $alertLevel)
                ->exists();

            if (!$existingAlert) {
                $user->notify(new BudgetAlertNotification($budget, $alertLevel, $percentage));
            }
        }
    }
}
