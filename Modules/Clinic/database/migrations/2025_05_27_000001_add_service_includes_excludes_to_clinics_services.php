<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clinics_services', function (Blueprint $table) {
            $table->json('service_includes')->nullable()->after('description');
            $table->json('service_excludes')->nullable()->after('service_includes');
        });
    }

    public function down()
    {
        Schema::table('clinics_services', function (Blueprint $table) {
            $table->dropColumn(['service_includes', 'service_excludes']);
        });
    }
};
