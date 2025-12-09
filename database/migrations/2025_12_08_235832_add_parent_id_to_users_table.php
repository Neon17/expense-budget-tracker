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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('family_name')->nullable()->after('parent_id');
            $table->string('role')->default('user')->after('family_name'); // user, parent, child
            $table->json('permissions')->nullable()->after('role'); // Scalable permissions JSON
            $table->boolean('is_active')->default(true)->after('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'family_name', 'role', 'permissions', 'is_active']);
        });
    }
};
