<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'parent_id',
        'family_name',
        'role',
        'permissions',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if the user can access Filament panel.
     * Only parent users (not child accounts) can access admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->role !== 'child';
    }

    /**
     * Get the parent user (for child accounts).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    /**
     * Get child users (for parent accounts).
     */
    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * Check if user is a parent account.
     */
    public function isParent(): bool
    {
        return $this->role === 'parent' || $this->children()->exists();
    }

    /**
     * Check if user is a child account.
     */
    public function isChild(): bool
    {
        return $this->role === 'child' && $this->parent_id !== null;
    }

    /**
     * Get the family name (from parent if child).
     */
    public function getFamilyDisplayNameAttribute(): ?string
    {
        if ($this->family_name) {
            return $this->family_name;
        }
        return $this->parent?->family_name;
    }

    /**
     * Check if user has a specific permission.
     * Scalable: permissions stored as JSON array.
     */
    public function hasPermission(string $permission): bool
    {
        // Parent users have all permissions
        if ($this->role === 'parent') {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Get all family members (including self).
     */
    public function getFamilyMembers()
    {
        if ($this->isParent()) {
            return $this->children()->get()->prepend($this);
        }

        if ($this->isChild() && $this->parent) {
            return $this->parent->children()->get()->prepend($this->parent);
        }

        return collect([$this]);
    }

    /**
     * Get the expenses for the user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the incomes for the user.
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Get the categories for the user.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the budgets for the user.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Get the current month's budget.
     */
    public function currentBudget(): HasOne
    {
        return $this->hasOne(Budget::class)
            ->where('month', now()->format('Y-m'));
    }

    /**
     * Get total expenses for current month.
     */
    public function getCurrentMonthExpensesAttribute(): float
    {
        return $this->expenses()
            ->month(now()->format('Y-m'))
            ->sum('amount');
    }

    /**
     * Get total income for current month.
     */
    public function getCurrentMonthIncomeAttribute(): float
    {
        return $this->incomes()
            ->month(now()->format('Y-m'))
            ->sum('amount');
    }

    /**
     * Get family groups owned by the user.
     */
    public function ownedFamilyGroups(): HasMany
    {
        return $this->hasMany(FamilyGroup::class, 'owner_id');
    }

    /**
     * Get all family groups the user belongs to.
     */
    public function familyGroups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(FamilyGroup::class, 'family_group_members')
            ->withPivot(['role', 'can_add_expenses', 'can_view_all', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * Check if user is owner of a family group.
     */
    public function isOwnerOf(FamilyGroup $familyGroup): bool
    {
        return $this->id === $familyGroup->owner_id;
    }

    /**
     * Check if user is admin of a family group.
     */
    public function isAdminOf(FamilyGroup $familyGroup): bool
    {
        $membership = $this->familyGroups()->where('family_group_id', $familyGroup->id)->first();
        return $membership && in_array($membership->pivot->role, ['owner', 'admin']);
    }

    /**
     * Check if user can create a family group.
     */
    public function canCreateFamilyGroup(): bool
    {
        return FamilyGroup::canUserCreate($this);
    }

    /**
     * Check if user can join a family group.
     */
    public function canJoinFamilyGroup(): bool
    {
        return FamilyGroup::canUserJoin($this);
    }

    /**
     * Check if user can access a specific family group.
     */
    public function canAccessFamilyGroup(int $familyGroupId): bool
    {
        return $this->familyGroups()->where('family_group_id', $familyGroupId)->exists()
            || $this->ownedFamilyGroups()->where('id', $familyGroupId)->exists();
    }

    /**
     * Get the family group the user belongs to (if any).
     * Returns the first/only family group since users can only belong to one.
     */
    public function getFamilyGroup(): ?FamilyGroup
    {
        return $this->familyGroups()->first();
    }

    /**
     * Check if user belongs to any family group.
     */
    public function belongsToFamilyGroup(): bool
    {
        return $this->familyGroups()->exists();
    }

    /**
     * Get the effective user for data access.
     * For child users, this returns the parent user (for shared dashboard).
     * For parent users, this returns themselves.
     */
    public function getDataOwner(): User
    {
        if ($this->isChild() && $this->parent) {
            return $this->parent;
        }
        return $this;
    }

    /**
     * Get all user IDs that share the same dashboard.
     * This includes the parent and all children.
     */
    public function getSharedDashboardUserIds(): array
    {
        if ($this->isChild() && $this->parent) {
            // If child, get parent and all siblings including self
            return $this->parent->getFamilyMembers()->pluck('id')->toArray();
        }
        
        if ($this->isParent()) {
            // If parent, get self and all children
            return $this->getFamilyMembers()->pluck('id')->toArray();
        }

        // Regular user without family
        return [$this->id];
    }

    /**
     * Scope to get expenses for the shared family dashboard.
     */
    public function getSharedExpenses()
    {
        $userIds = $this->getSharedDashboardUserIds();
        return Expense::whereIn('user_id', $userIds);
    }

    /**
     * Scope to get incomes for the shared family dashboard.
     */
    public function getSharedIncomes()
    {
        $userIds = $this->getSharedDashboardUserIds();
        return Income::whereIn('user_id', $userIds);
    }

    /**
     * Scope to get budgets for the shared family dashboard.
     */
    public function getSharedBudgets()
    {
        $dataOwner = $this->getDataOwner();
        return Budget::where('user_id', $dataOwner->id);
    }

    /**
     * Scope to get categories for the shared family dashboard.
     */
    public function getSharedCategories()
    {
        $dataOwner = $this->getDataOwner();
        return Category::where('user_id', $dataOwner->id);
    }
}
