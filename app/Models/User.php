<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        ];
    }

    /**
     * Determine if the user can access Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
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
}
