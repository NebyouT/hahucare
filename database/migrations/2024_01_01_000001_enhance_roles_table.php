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
        // Add columns to roles table if they don't exist
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'title')) {
                    $table->string('title')->nullable()->after('name');
                }
                if (!Schema::hasColumn('roles', 'description')) {
                    $table->text('description')->nullable()->after('title');
                }
                if (!Schema::hasColumn('roles', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('description');
                }
                if (!Schema::hasColumn('roles', 'is_fixed')) {
                    $table->boolean('is_fixed')->default(false)->after('is_active');
                }
            });
        }

        // Update existing roles with proper titles and fixed status
        $rolesToUpdate = [
            'admin' => ['title' => 'Administrator', 'is_fixed' => true],
            'demo_admin' => ['title' => 'Demo Administrator', 'is_fixed' => true],
            'doctor' => ['title' => 'Doctor', 'is_fixed' => true],
            'receptionist' => ['title' => 'Receptionist', 'is_fixed' => true],
            'vendor' => ['title' => 'Vendor', 'is_fixed' => true],
            'pharma' => ['title' => 'Pharmacy', 'is_fixed' => true],
            'user' => ['title' => 'Patient/User', 'is_fixed' => true],
        ];

        foreach ($rolesToUpdate as $roleName => $data) {
            \DB::table('roles')
                ->where('name', $roleName)
                ->update([
                    'title' => $data['title'],
                    'is_fixed' => $data['is_fixed'],
                    'is_active' => true
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn(['title', 'description', 'is_active', 'is_fixed']);
            });
        }
    }
};
