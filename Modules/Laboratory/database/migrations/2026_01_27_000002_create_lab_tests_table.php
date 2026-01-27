<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->string('test_code')->unique();
            $table->string('test_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('discount_type')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('preparation_instructions')->nullable();
            $table->text('normal_range')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('reporting_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('category_id')->references('id')->on('lab_test_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
