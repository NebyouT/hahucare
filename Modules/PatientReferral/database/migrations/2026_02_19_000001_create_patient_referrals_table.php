<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table already exists in production, skip creation
        // This migration is kept for reference only
    }

    public function down(): void
    {
        // No-op since table already exists
    }
};
