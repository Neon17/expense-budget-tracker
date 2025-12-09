<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class FamilyUserController extends Controller
{
    use ApiResponse;

    /**
     * Get all family members (children) for the authenticated parent user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Only parent users can view family members
        if (!$user->isParent() && $user->role !== 'parent') {
            return $this->forbiddenResponse('Only parent accounts can view family members');
        }

        $children = $user->children()->get();

        return $this->successResponse(
            new UserCollection($children),
            'Family members retrieved successfully'
        );
    }

    /**
     * Create a child user account.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Child users cannot create other children
        if ($user->isChild()) {
            return $this->forbiddenResponse('Child users cannot create other child accounts');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        // If user doesn't have a family name, they need to set one first
        if (!$user->family_name && $user->role !== 'parent') {
            return $this->validationErrorResponse(
                ['family_name' => ['Please set a family name first using the update-family endpoint']],
                'Family name required'
            );
        }

        // Update parent role if not already set
        if ($user->role === 'user') {
            $user->update(['role' => 'parent']);
        }

        $child = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'currency' => $user->currency, // Inherit parent's currency
            'parent_id' => $user->id,
            'family_name' => $user->family_name,
            'role' => 'child',
            'permissions' => $request->permissions ?? ['view_expenses', 'add_expenses'],
            'is_active' => true,
        ]);

        return $this->createdResponse(
            new UserResource($child),
            'Child account created successfully'
        );
    }

    /**
     * Get a specific child user.
     */
    public function show(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        return $this->successResponse(
            new UserResource($child),
            'Child account retrieved successfully'
        );
    }

    /**
     * Update a child user account.
     */
    public function update(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $child->id],
            'password' => ['sometimes', Password::defaults()],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $updateData = $request->only(['name', 'email', 'permissions', 'is_active']);

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $child->update($updateData);

        return $this->successResponse(
            new UserResource($child->fresh()),
            'Child account updated successfully'
        );
    }

    /**
     * Delete (deactivate) a child user account.
     */
    public function destroy(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        // Soft delete - deactivate instead of hard delete
        $child->update(['is_active' => false]);

        // Revoke all tokens
        $child->tokens()->delete();

        return $this->deletedResponse('Child account deactivated successfully');
    }

    /**
     * Permanently delete a child user account.
     */
    public function forceDestroy(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        // Delete all related data first (expenses, incomes, etc.)
        $child->expenses()->delete();
        $child->incomes()->delete();
        $child->categories()->delete();
        $child->budgets()->delete();
        $child->tokens()->delete();

        // Hard delete the user
        $child->delete();

        return $this->deletedResponse('Child account permanently deleted');
    }

    /**
     * Set or update family name for the parent account.
     */
    public function updateFamily(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'family_name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        // Update parent's family name
        $user->update([
            'family_name' => $request->family_name,
            'role' => 'parent',
        ]);

        // Update all children's family name
        $user->children()->update([
            'family_name' => $request->family_name,
        ]);

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Family name updated successfully'
        );
    }

    /**
     * Get family statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isParent()) {
            return $this->forbiddenResponse('Only parent accounts can view family statistics');
        }

        $familyMembers = $user->getFamilyMembers();
        $familyMemberIds = $familyMembers->pluck('id');

        $currentMonth = now()->format('Y-m');

        $stats = [
            'family_name' => $user->family_name,
            'total_members' => $familyMembers->count(),
            'active_members' => $familyMembers->where('is_active', true)->count(),
            'total_expenses_this_month' => \App\Models\Expense::whereIn('user_id', $familyMemberIds)
                ->month($currentMonth)
                ->sum('amount'),
            'total_income_this_month' => \App\Models\Income::whereIn('user_id', $familyMemberIds)
                ->month($currentMonth)
                ->sum('amount'),
            'members_breakdown' => $familyMembers->map(function ($member) use ($currentMonth) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'role' => $member->role,
                    'expenses_this_month' => $member->expenses()
                        ->month($currentMonth)
                        ->sum('amount'),
                    'income_this_month' => $member->incomes()
                        ->month($currentMonth)
                        ->sum('amount'),
                ];
            }),
        ];

        return $this->successResponse($stats, 'Family statistics retrieved successfully');
    }

    /**
     * Update child permissions.
     */
    public function updatePermissions(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        $validator = Validator::make($request->all(), [
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->toArray());
        }

        $child->update([
            'permissions' => $request->permissions,
        ]);

        return $this->successResponse(
            new UserResource($child->fresh()),
            'Permissions updated successfully'
        );
    }

    /**
     * Get available permissions list.
     */
    public function availablePermissions(): JsonResponse
    {
        $permissions = [
            'view_expenses' => 'View expenses',
            'add_expenses' => 'Add expenses',
            'edit_expenses' => 'Edit own expenses',
            'delete_expenses' => 'Delete own expenses',
            'view_incomes' => 'View incomes',
            'add_incomes' => 'Add incomes',
            'edit_incomes' => 'Edit own incomes',
            'delete_incomes' => 'Delete own incomes',
            'view_categories' => 'View categories',
            'manage_categories' => 'Manage categories',
            'view_budgets' => 'View budgets',
            'view_analytics' => 'View analytics',
            'view_family_data' => 'View family members data',
        ];

        return $this->successResponse($permissions, 'Available permissions retrieved');
    }

    /**
     * Reactivate a deactivated child account.
     */
    public function reactivate(Request $request, User $child): JsonResponse
    {
        $user = $request->user();

        // Verify the child belongs to this parent
        if ($child->parent_id !== $user->id) {
            return $this->forbiddenResponse('This user is not part of your family');
        }

        $child->update(['is_active' => true]);

        return $this->successResponse(
            new UserResource($child->fresh()),
            'Child account reactivated successfully'
        );
    }

    /**
     * Get family overview - works for both parents and children.
     * Children can see their parent and siblings.
     * Parents can see their children.
     */
    public function overview(Request $request): JsonResponse
    {
        $user = $request->user();
        $dataOwner = $user->getDataOwner();
        
        $isParent = !$user->isChild();
        $isChild = $user->isChild();
        
        // Get parent info
        $parent = $isChild ? $user->parent : $user;
        
        // Get siblings (other children of the same parent, excluding self)
        $siblings = $isChild 
            ? User::where('parent_id', $parent->id)
                ->where('id', '!=', $user->id)
                ->get()
            : collect();
        
        // Get all children (for parent view)
        $children = $isParent ? $user->children()->get() : collect();
        
        // Get all family members
        $familyMembers = $user->getFamilyMembers();
        
        // Get the family group if exists
        $familyGroup = $user->getFamilyGroup() ?? $dataOwner->ownedFamilyGroups()->first();
        
        // Calculate stats
        $totalMembers = $familyMembers->count();
        $activeMembers = $familyMembers->filter(fn($m) => $m->is_active !== false)->count();
        $inactiveMembers = $totalMembers - $activeMembers;
        
        $response = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_parent' => $isParent,
                'is_child' => $isChild,
            ],
            'family' => [
                'name' => $parent->family_name ?? $familyGroup?->name ?? 'My Family',
                'currency' => $dataOwner->currency ?? 'NPR',
                'total_members' => $totalMembers,
                'active_members' => $activeMembers,
                'inactive_members' => $inactiveMembers,
            ],
            'parent' => $parent ? [
                'id' => $parent->id,
                'name' => $parent->name,
                'email' => $parent->email,
                'currency' => $parent->currency ?? 'NPR',
                'is_active' => $parent->is_active !== false,
                'created_at' => $parent->created_at,
            ] : null,
            'siblings' => $siblings->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'email' => $s->email,
                'is_active' => $s->is_active !== false,
                'created_at' => $s->created_at,
            ])->values(),
            'children' => $children->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'is_active' => $c->is_active !== false,
                'permissions' => $c->permissions ?? [],
                'created_at' => $c->created_at,
            ])->values(),
            'family_group' => $familyGroup ? [
                'id' => $familyGroup->id,
                'name' => $familyGroup->name,
                'description' => $familyGroup->description,
                'invite_code' => $isParent ? $familyGroup->invite_code : null, // Only show invite code to parents
                'shared_budget' => $familyGroup->shared_budget,
                'is_active' => $familyGroup->is_active,
                'created_at' => $familyGroup->created_at,
            ] : null,
        ];

        return $this->successResponse($response, 'Family overview retrieved successfully');
    }
}
