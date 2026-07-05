<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('excel_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // 'export' or 'import'
            $table->string('module'); // 'product', 'stockin', 'stockout', 'adjustment'
            $table->string('status')->default('pending'); // 'pending', 'processing', 'completed', 'failed'
            $table->string('file_path')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('excel_jobs');
    }
};
