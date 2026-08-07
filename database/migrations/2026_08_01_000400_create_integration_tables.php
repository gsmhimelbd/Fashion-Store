<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) { $table->id(); $table->morphs('tokenable'); $table->string('name'); $table->string('token',64)->unique(); $table->text('abilities')->nullable(); $table->timestamp('last_used_at')->nullable(); $table->timestamp('expires_at')->nullable()->index(); $table->timestamps(); });
        Schema::create('notifications', function (Blueprint $table) { $table->uuid('id')->primary(); $table->string('type'); $table->morphs('notifiable'); $table->text('data'); $table->timestamp('read_at')->nullable(); $table->timestamps(); });
        Schema::create('media', function (Blueprint $table) { $table->id(); $table->uuid('uuid')->nullable()->unique(); $table->string('collection_name'); $table->string('name'); $table->string('file_name'); $table->string('mime_type')->nullable(); $table->string('disk'); $table->string('conversions_disk')->nullable(); $table->unsignedBigInteger('size'); $table->json('manipulations'); $table->json('custom_properties'); $table->json('generated_conversions'); $table->json('responsive_images'); $table->unsignedInteger('order_column')->nullable()->index(); $table->nullableMorphs('model'); $table->timestamps(); });
        Schema::create('permissions', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('guard_name'); $table->timestamps(); $table->unique(['name','guard_name']); });
        Schema::create('roles', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('guard_name'); $table->timestamps(); $table->unique(['name','guard_name']); });
        Schema::create('model_has_permissions', function (Blueprint $table) { $table->unsignedBigInteger('permission_id'); $table->string('model_type'); $table->unsignedBigInteger('model_id'); $table->index(['model_id','model_type']); $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete(); $table->primary(['permission_id','model_id','model_type']); });
        Schema::create('model_has_roles', function (Blueprint $table) { $table->unsignedBigInteger('role_id'); $table->string('model_type'); $table->unsignedBigInteger('model_id'); $table->index(['model_id','model_type']); $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete(); $table->primary(['role_id','model_id','model_type']); });
        Schema::create('role_has_permissions', function (Blueprint $table) { $table->unsignedBigInteger('permission_id'); $table->unsignedBigInteger('role_id'); $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete(); $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete(); $table->primary(['permission_id','role_id']); });
    }
    public function down(): void { Schema::dropIfExists('role_has_permissions'); Schema::dropIfExists('model_has_roles'); Schema::dropIfExists('model_has_permissions'); Schema::dropIfExists('roles'); Schema::dropIfExists('permissions'); Schema::dropIfExists('media'); Schema::dropIfExists('notifications'); Schema::dropIfExists('personal_access_tokens'); }
};
