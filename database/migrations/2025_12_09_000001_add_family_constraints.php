<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_active column to family_groups if not exists
        if (!Schema::hasColumn('family_groups', 'is_active')) {
            Schema::table('family_groups', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('currency');
            });
        }

        // Add unique constraint on user_id in family_group_members
        // This ensures a user can only belong to ONE family group
        Schema::table('family_group_members', function (Blueprint $table) {
            // First drop the existing composite unique if it exists
            // Then add unique constraint on just user_id
            $table->unique('user_id', 'family_group_members_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('family_group_members', function (Blueprint $table) {
            $table->dropUnique('family_group_members_user_unique');
        });

        if (Schema::hasColumn('family_groups', 'is_active')) {
            Schema::table('family_groups', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
