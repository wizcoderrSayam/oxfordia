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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 20)->default('pending');
            $table->string('gateway', 30);
            $table->string('gateway_reference')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
