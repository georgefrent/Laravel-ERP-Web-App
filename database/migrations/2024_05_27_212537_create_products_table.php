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
        Schema::create('products', function (Blueprint $table) {
            $table->id('product_id');
            $table->string('product_name', 255);
            $table->string('category_name', 255);
            $table->string('brand', 255);
            $table->string('model', 255)->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity_in_stock');
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamp('entered_at');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('category_name')->references('category_name')->on('product_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
