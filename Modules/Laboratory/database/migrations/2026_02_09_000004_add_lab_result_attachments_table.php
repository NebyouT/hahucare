<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_result_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_result_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size');
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->foreign('lab_result_id')->references('id')->on('lab_results')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_result_attachments');
    }
};
