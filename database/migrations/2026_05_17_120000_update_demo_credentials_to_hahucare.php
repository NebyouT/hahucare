<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update doctor email from kivicare.com to hahucare.com
        DB::table('users')
            ->where('email', 'doctor@kivicare.com')
            ->update(['email' => 'doctor@hahucare.com']);

        // Update receptionist email from kivicare.com to hahucare.com
        DB::table('users')
            ->where('email', 'receptionist@kivicare.com')
            ->update(['email' => 'receptionist@hahucare.com']);

        // Update demo admin email from kivicare.com to hahucare.com
        DB::table('users')
            ->where('email', 'demo@kivicare.com')
            ->update(['email' => 'demo@hahucare.com']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert doctor email back to kivicare.com
        DB::table('users')
            ->where('email', 'doctor@hahucare.com')
            ->update(['email' => 'doctor@kivicare.com']);

        // Revert receptionist email back to kivicare.com
        DB::table('users')
            ->where('email', 'receptionist@hahucare.com')
            ->update(['email' => 'receptionist@kivicare.com']);

        // Revert demo admin email back to kivicare.com
        DB::table('users')
            ->where('email', 'demo@hahucare.com')
            ->update(['email' => 'demo@kivicare.com']);
    }
};
