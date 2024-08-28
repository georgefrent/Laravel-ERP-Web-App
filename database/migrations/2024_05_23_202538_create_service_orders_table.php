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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('device_name');
            $table->string('customer_name');
            $table->enum('status', ['not started', 'in progress', 'finished']);
            $table->integer('progress')->default(0);
            $table->decimal('price', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->enum('payment_status', ['unpaid', 'completed']);
            $table->timestamp('entered_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
