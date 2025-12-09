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
        'is_active',
    ];

    protected $casts = [
        'shared_budget' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->invite_code)) {
                $model->invite_code = self::generateInviteCode();
            }
        });

        // When a family group is created, automatically add owner as member
        static::created(function ($model) {
            $model->addMember($model->owner_id, 'owner');
        });
    }

    /**
     * Generate a unique invite code.
     */
    public static function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (self::where('invite_code', $code)->exists());
        
        return $code;
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
        $this->update(['invite_code' => self::generateInviteCode()]);
    }

    /**
     * Check if a user is a member of this family group.
     */
    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Add a member to the family group.
     */
    public function addMember(int $userId, string $role = 'member', array $permissions = []): void
    {
        if ($this->hasMember($userId)) {
            return;
        }

        $this->members()->attach($userId, [
            'role' => $role,
            'can_add_expenses' => $permissions['can_add_expenses'] ?? true,
            'can_view_all' => $permissions['can_view_all'] ?? true,
            'joined_at' => now(),
        ]);
    }

    /**
     * Remove a member from the family group.
     */
    public function removeMember(int $userId): void
    {
        $this->members()->detach($userId);
    }

    /**
     * Check if user can create a family group.
     * - Child users cannot create family groups
     * - Users who already own a family group cannot create another one
     * - Users who are already members of a family group cannot create one
     */
    public static function canUserCreate(User $user): bool
    {
        // Child users cannot create family groups
        if ($user->isChild()) {
            return false;
        }

        // Check if user already owns a family group
        if (self::where('owner_id', $user->id)->exists()) {
            return false;
        }

        // Check if user is already a member of any family group
        if ($user->familyGroups()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Check if a user can join this family group.
     * - Child users cannot join family groups (they are part of parent-child family)
     * - Users who already belong to a family group cannot join another one
     */
    public static function canUserJoin(User $user): bool
    {
        // Child users cannot join family groups
        if ($user->isChild()) {
            return false;
        }

        // Check if user is already a member of any family group
        if ($user->familyGroups()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get validation error message for why user cannot create family.
     */
    public static function getCannotCreateReason(User $user): ?string
    {
        if ($user->isChild()) {
            return 'Child users cannot create family groups. Only parent users can create families.';
        }

        if (self::where('owner_id', $user->id)->exists()) {
            return 'You already own a family group. Each user can only own one family group.';
        }

        if ($user->familyGroups()->exists()) {
            return 'You are already a member of a family group. You must leave your current family group before creating a new one.';
        }

        return null;
    }

    /**
     * Get validation error message for why user cannot join a family.
     */
    public static function getCannotJoinReason(User $user): ?string
    {
        if ($user->isChild()) {
            return 'Child users cannot join family groups. You are already part of your parent\'s family.';
        }

        if ($user->familyGroups()->exists()) {
            return 'You are already a member of a family group. You must leave your current family group before joining another one.';
        }

        return null;
    }
}
