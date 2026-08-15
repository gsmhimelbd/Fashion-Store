<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 100);
            $table->string('phone', 20);
            $table->string('whatsapp', 20)->nullable();
            $table->string('district', 100);
            $table->string('upazila', 100);
            $table->text('address');
            $table->text('notes')->nullable();
            $table->string('payment_method', 50)->default('cod'); // cod, bkash, nagad, rocket
            $table->string('payment_number', 50)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_charge', 10, 2)->default(50.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('grand_total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
