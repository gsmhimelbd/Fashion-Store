<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) { $table->id(); $table->foreignId('vcard_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('email'); $table->string('phone', 40)->nullable(); $table->timestamp('starts_at'); $table->timestamp('ends_at')->nullable(); $table->string('timezone')->default('UTC'); $table->text('message')->nullable(); $table->string('status')->default('pending')->index(); $table->string('meeting_url')->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); $table->index(['vcard_id','starts_at']); });
        Schema::create('leads', function (Blueprint $table) { $table->id(); $table->foreignId('vcard_id')->constrained()->cascadeOnDelete(); $table->string('name'); $table->string('email')->nullable(); $table->string('phone', 40)->nullable(); $table->string('company')->nullable(); $table->text('message')->nullable(); $table->string('source')->nullable(); $table->string('status')->default('new')->index(); $table->timestamp('consented_at')->nullable(); $table->json('metadata')->nullable(); $table->timestamps(); });
        Schema::create('analytics', function (Blueprint $table) { $table->id(); $table->foreignId('vcard_id')->constrained()->cascadeOnDelete(); $table->string('event', 50)->index(); $table->string('target')->nullable(); $table->uuid('session_id')->nullable()->index(); $table->char('ip_hash', 64)->nullable(); $table->text('referrer')->nullable(); $table->string('country', 2)->nullable(); $table->string('city')->nullable(); $table->string('device', 30)->nullable(); $table->string('browser', 40)->nullable(); $table->timestamp('occurred_at')->useCurrent()->index(); $table->json('metadata')->nullable(); $table->timestamps(); $table->index(['vcard_id','event','occurred_at']); });
        Schema::create('custom_domains', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('vcard_id')->nullable()->constrained()->nullOnDelete(); $table->string('domain')->unique(); $table->string('status')->default('pending')->index(); $table->string('verification_token', 64)->unique(); $table->timestamp('verified_at')->nullable(); $table->string('ssl_status')->default('pending'); $table->timestamp('ssl_expires_at')->nullable(); $table->boolean('is_primary')->default(false); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('custom_domains'); Schema::dropIfExists('analytics'); Schema::dropIfExists('leads'); Schema::dropIfExists('appointments'); }
};
