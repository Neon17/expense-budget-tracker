<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class FamilyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'invite_code',
        'owner_id',
        'shared_budget',
        'currency',
    ];

    protected $casts = [
        'shared_budget' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->invite_code)) {
                $model->invite_code = Str::upper(Str::random(8));
            }
        });
    }

    /**
     * Get the owner of the family group.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the family group.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_group_members')
            ->withPivot(['role', 'can_add_expenses', 'can_view_all', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Get total expenses for the family group in a specific month.
     */
    public function getTotalExpensesAttribute(): float
    {
        $currentMonth = now()->format('Y-m');
        return $this->members->sum(function ($member) use ($currentMonth) {
            return $member->expenses()->month($currentMonth)->sum('amount');
        });
    }

    /**
     * Get total income for the family group in a specific month.
     */
    public function getTotalIncomeAttribute(): float
    {
        $currentMonth = now()->format('Y-m');
        return $this->members->sum(function ($member) use ($currentMonth) {
            return $member->incomes()->month($currentMonth)->sum('amount');
        });
    }

    /**
     * Get budget usage percentage.
     */
    public function getBudgetUsageAttribute(): float
    {
        if (!$this->shared_budget || $this->shared_budget <= 0) {
            return 0;
        }
        return round(($this->total_expenses / $this->shared_budget) * 100, 2);
    }

    /**
     * Regenerate invite code.
     */
    public function regenerateInviteCode(): void
    {
        $this->update(['invite_code' => Str::upper(Str::random(8))]);
    }
}
