<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->text('description')->nullable(); $table->decimal('price_monthly', 12, 2)->default(0); $table->decimal('price_yearly', 12, 2)->default(0); $table->string('currency', 3)->default('USD'); $table->json('features')->nullable(); $table->json('limits')->nullable(); $table->boolean('is_active')->default(true)->index(); $table->unsignedSmallInteger('sort_order')->default(0); $table->timestamps(); });
        Schema::create('subscriptions', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('plan_id')->constrained(); $table->string('provider')->default('manual'); $table->string('provider_id')->nullable()->index(); $table->string('status')->default('pending')->index(); $table->string('billing_cycle')->default('monthly'); $table->timestamp('trial_ends_at')->nullable(); $table->timestamp('starts_at')->nullable(); $table->timestamp('ends_at')->nullable(); $table->timestamp('renews_at')->nullable(); $table->timestamp('cancelled_at')->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); });
        Schema::create('transactions', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete(); $table->string('reference')->unique(); $table->string('provider', 40); $table->string('provider_reference')->nullable()->index(); $table->string('type')->default('subscription'); $table->decimal('amount', 12, 2); $table->string('currency', 3)->default('USD'); $table->string('status')->default('pending')->index(); $table->timestamp('paid_at')->nullable(); $table->timestamp('verified_at')->nullable(); $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete(); $table->json('metadata')->nullable(); $table->timestamps(); });
        Schema::create('coupons', function (Blueprint $table) { $table->id(); $table->string('code')->unique(); $table->string('type')->default('percent'); $table->decimal('value', 12, 2); $table->string('currency', 3)->nullable(); $table->unsignedInteger('max_redemptions')->nullable(); $table->unsignedInteger('redemptions')->default(0); $table->timestamp('starts_at')->nullable(); $table->timestamp('expires_at')->nullable(); $table->json('applicable_plan_ids')->nullable(); $table->boolean('is_active')->default(true)->index(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('coupons'); Schema::dropIfExists('transactions'); Schema::dropIfExists('subscriptions'); Schema::dropIfExists('plans'); }
};
