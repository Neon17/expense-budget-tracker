<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'monthly_limit',
        'month',
        'currency',
    ];

    protected $casts = [
        'monthly_limit' => 'decimal:2',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the spent amount for this budget month.
     */
    public function getSpentAttribute(): float
    {
        return $this->user->expenses()
            ->month($this->month)
            ->sum('amount');
    }

    /**
     * Get the remaining amount for this budget.
     */
    public function getRemainingAttribute(): float
    {
        return max(0, $this->monthly_limit - $this->spent);
    }

    /**
     * Get the usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->monthly_limit <= 0) {
            return 0;
        }
        return round(($this->spent / $this->monthly_limit) * 100, 2);
    }

    /**
     * Get the budget status.
     */
    public function getStatusAttribute(): string
    {
        $percentage = $this->usage_percentage;
        
        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 90) {
            return 'critical';
        } elseif ($percentage >= 70) {
            return 'warning';
        }
        
        return 'safe';
    }

    /**
     * Get or create current month's budget.
     */
    public static function currentForUser(User $user): ?self
    {
        $currentMonth = now()->format('Y-m');
        
        return self::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->first();
    }
}
