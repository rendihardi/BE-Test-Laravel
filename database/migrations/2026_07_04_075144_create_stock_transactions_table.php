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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('transaction_type', ['IN', 'OUT']);
            $table->integer('qty');
            $table->dateTime('transaction_date');
            $table->string('product_name', 150);
            $table->string('image')->nullable();
            $table->string('category_name', 100);
            $table->decimal('price', 15, 2);
            $table->string('reference_document', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignUuid('created_by')->references('uuid')->on('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
