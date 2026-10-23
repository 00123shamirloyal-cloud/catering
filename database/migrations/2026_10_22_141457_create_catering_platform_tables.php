<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 40)->default('staff')->after('password')->index();
        });

        Schema::create('content_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 40)->index();
            $table->string('title');
            $table->string('slug');
            $table->string('category')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('image_path')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_demo')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['type', 'slug']);
            $table->index(['type', 'status', 'sort_order']);
        });

        Schema::create('site_settings', function (Blueprint $table): void {
            $table->string('key')->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone', 40)->nullable()->index();
            $table->string('preferred_contact', 30)->default('phone');
            $table->string('lead_source', 80)->default('website');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['name', 'created_at']);
        });

        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->string('idempotency_token', 64)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 40);
            $table->string('preferred_contact', 30);
            $table->string('event_type', 100);
            $table->date('event_date')->index();
            $table->date('alternative_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('guest_count');
            $table->string('city', 120);
            $table->string('venue')->nullable();
            $table->string('setting', 30)->nullable();
            $table->string('budget_range', 80)->nullable();
            $table->foreignId('package_id')->nullable()->constrained('content_entries')->nullOnDelete();
            $table->text('dietary_preferences')->nullable();
            $table->text('requirements')->nullable();
            $table->text('message')->nullable();
            $table->boolean('privacy_accepted');
            $table->string('status', 40)->default('new')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('next_follow_up_at')->nullable()->index();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('inquiry_content_entry', function (Blueprint $table): void {
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_entry_id')->constrained()->cascadeOnDelete();
            $table->string('selection_type', 30)->default('service');
            $table->primary(['inquiry_id', 'content_entry_id']);
        });

        Schema::create('follow_up_tasks', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('due_at')->nullable()->index();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 24)->default('open')->index();
            $table->text('completion_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status', 24)->default('unread')->index();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('subject');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('description');
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('follow_up_tasks');
        Schema::dropIfExists('inquiry_content_entry');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('content_entries');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
