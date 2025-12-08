<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyGroupController extends Controller
{
    /**
     * Get all family groups for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $familyGroups = $user->familyGroups()
            ->with(['owner:id,name,email', 'members:id,name,email'])
            ->withCount('members')
            ->get()
            ->map(function ($group) use ($user) {
                $pivot = $group->members->firstWhere('id', $user->id)?->pivot;
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'owner' => [
                        'id' => $group->owner->id,
                        'name' => $group->owner->name,
                        'email' => $group->owner->email,
                    ],
                    'members_count' => $group->members_count,
                    'currency' => $group->currency,
                    'is_owner' => $group->owner_id === $user->id,
                    'my_role' => $pivot?->role ?? 'owner',
                    'joined_at' => $pivot?->joined_at,
                    'is_active' => $group->is_active,
                    'created_at' => $group->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $familyGroups,
        ]);
    }

    /**
     * Create a new family group.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|size:3',
        ]);

        $user = $request->user();

        $familyGroup = FamilyGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $user->id,
            'invite_code' => FamilyGroup::generateInviteCode(),
            'currency' => $validated['currency'] ?? $user->currency ?? 'NPR',
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Family group created successfully',
            'data' => [
                'id' => $familyGroup->id,
                'name' => $familyGroup->name,
                'description' => $familyGroup->description,
                'invite_code' => $familyGroup->invite_code,
                'currency' => $familyGroup->currency,
            ],
        ], 201);
    }

    /**
     * Get a specific family group.
     */
    public function show(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if (!$user->canAccessFamilyGroup($familyGroup->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this family group',
            ], 403);
        }

        $familyGroup->load(['owner:id,name,email', 'members:id,name,email']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $familyGroup->id,
                'name' => $familyGroup->name,
                'description' => $familyGroup->description,
                'owner' => [
                    'id' => $familyGroup->owner->id,
                    'name' => $familyGroup->owner->name,
                    'email' => $familyGroup->owner->email,
                ],
                'members' => $familyGroup->members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'email' => $member->email,
                        'role' => $member->pivot->role,
                        'joined_at' => $member->pivot->joined_at,
                    ];
                }),
                'invite_code' => $familyGroup->owner_id === $user->id ? $familyGroup->invite_code : null,
                'currency' => $familyGroup->currency,
                'is_active' => $familyGroup->is_active,
                'created_at' => $familyGroup->created_at,
                'updated_at' => $familyGroup->updated_at,
            ],
        ]);
    }

    /**
     * Update a family group.
     */
    public function update(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can update the family group',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'currency' => 'nullable|string|size:3',
            'is_active' => 'sometimes|boolean',
        ]);

        $familyGroup->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Family group updated successfully',
            'data' => [
                'id' => $familyGroup->id,
                'name' => $familyGroup->name,
                'description' => $familyGroup->description,
                'currency' => $familyGroup->currency,
                'is_active' => $familyGroup->is_active,
            ],
        ]);
    }

    /**
     * Delete a family group.
     */
    public function destroy(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can delete the family group',
            ], 403);
        }

        $familyGroup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Family group deleted successfully',
        ]);
    }

    /**
     * Join a family group using invite code.
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $user = $request->user();

        $familyGroup = FamilyGroup::where('invite_code', strtoupper($validated['invite_code']))
            ->where('is_active', true)
            ->first();

        if (!$familyGroup) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired invite code',
            ], 404);
        }

        // Check if already a member
        if ($familyGroup->hasMember($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already a member of this family group',
            ], 422);
        }

        // Add user to family group
        $familyGroup->addMember($user->id, 'member');

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the family group',
            'data' => [
                'id' => $familyGroup->id,
                'name' => $familyGroup->name,
                'owner' => $familyGroup->owner->name,
            ],
        ]);
    }

    /**
     * Leave a family group.
     */
    public function leave(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if ($familyGroup->owner_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'The owner cannot leave the family group. Transfer ownership first or delete the group.',
            ], 422);
        }

        if (!$familyGroup->hasMember($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are not a member of this family group',
            ], 422);
        }

        $familyGroup->removeMember($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Successfully left the family group',
        ]);
    }

    /**
     * Remove a member from family group (owner only).
     */
    public function removeMember(Request $request, FamilyGroup $familyGroup, User $member)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can remove members',
            ], 403);
        }

        if ($member->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot remove yourself. Transfer ownership first or delete the group.',
            ], 422);
        }

        if (!$familyGroup->hasMember($member->id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this family group',
            ], 422);
        }

        $familyGroup->removeMember($member->id);

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully',
        ]);
    }

    /**
     * Regenerate invite code (owner only).
     */
    public function regenerateInviteCode(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can regenerate the invite code',
            ], 403);
        }

        $familyGroup->update([
            'invite_code' => FamilyGroup::generateInviteCode(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invite code regenerated successfully',
            'data' => [
                'invite_code' => $familyGroup->invite_code,
            ],
        ]);
    }

    /**
     * Update member role (owner only).
     */
    public function updateMemberRole(Request $request, FamilyGroup $familyGroup, User $member)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can update member roles',
            ], 403);
        }

        $validated = $request->validate([
            'role' => 'required|in:admin,member,viewer',
        ]);

        if (!$familyGroup->hasMember($member->id)) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a member of this family group',
            ], 422);
        }

        $familyGroup->members()->updateExistingPivot($member->id, [
            'role' => $validated['role'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member role updated successfully',
        ]);
    }

    /**
     * Transfer ownership (owner only).
     */
    public function transferOwnership(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if ($familyGroup->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Only the owner can transfer ownership',
            ], 403);
        }

        $validated = $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = User::find($validated['new_owner_id']);

        if (!$familyGroup->hasMember($newOwner->id)) {
            return response()->json([
                'success' => false,
                'message' => 'New owner must be a member of the family group',
            ], 422);
        }

        DB::transaction(function () use ($familyGroup, $user, $newOwner) {
            // Remove new owner from members
            $familyGroup->removeMember($newOwner->id);
            
            // Add current owner as admin member
            $familyGroup->addMember($user->id, 'admin');
            
            // Update ownership
            $familyGroup->update(['owner_id' => $newOwner->id]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Ownership transferred successfully',
        ]);
    }

    /**
     * Get family group statistics.
     */
    public function statistics(Request $request, FamilyGroup $familyGroup)
    {
        $user = $request->user();

        if (!$user->canAccessFamilyGroup($familyGroup->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this family group',
            ], 403);
        }

        $currentMonth = now()->format('Y-m');

        // Get all member IDs including owner
        $memberIds = $familyGroup->members()->pluck('users.id')->push($familyGroup->owner_id)->unique();

        $totalExpenses = DB::table('expenses')
            ->whereIn('user_id', $memberIds)
            ->whereRaw("strftime('%Y-%m', date) = ?", [$currentMonth])
            ->sum('amount');

        $totalIncome = DB::table('incomes')
            ->whereIn('user_id', $memberIds)
            ->whereRaw("strftime('%Y-%m', date) = ?", [$currentMonth])
            ->sum('amount');

        $memberStats = $memberIds->map(function ($memberId) use ($currentMonth) {
            $user = User::find($memberId);
            $expenses = DB::table('expenses')
                ->where('user_id', $memberId)
                ->whereRaw("strftime('%Y-%m', date) = ?", [$currentMonth])
                ->sum('amount');
            $income = DB::table('incomes')
                ->where('user_id', $memberId)
                ->whereRaw("strftime('%Y-%m', date) = ?", [$currentMonth])
                ->sum('amount');

            return [
                'user_id' => $memberId,
                'user_name' => $user->name,
                'expenses' => (float) $expenses,
                'income' => (float) $income,
                'net' => (float) $income - (float) $expenses,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $currentMonth,
                'total_expenses' => (float) $totalExpenses,
                'total_income' => (float) $totalIncome,
                'net_savings' => (float) $totalIncome - (float) $totalExpenses,
                'members_count' => $memberIds->count(),
                'member_stats' => $memberStats,
                'currency' => $familyGroup->currency,
            ],
        ]);
    }
}
