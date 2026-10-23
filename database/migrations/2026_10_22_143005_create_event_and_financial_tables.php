<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('reference', 24)->unique();
            $table->foreignId('inquiry_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('category', 100);
            $table->date('event_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('city', 120)->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->foreignId('event_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('tentative_hold')->index();
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hold_expires_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['event_date', 'venue', 'status']);
        });

        Schema::table('follow_up_tasks', function (Blueprint $table): void {
            $table->foreignId('event_id')->nullable()->after('inquiry_id')->constrained()->nullOnDelete();
        });

        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('type', 60);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->string('timezone', 80)->default('UTC');
            $table->foreignId('assigned_to')->constrained('users')->restrictOnDelete();
            $table->string('location')->nullable();
            $table->string('meeting_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 24)->default('scheduled')->index();
            $table->timestamp('reminder_at')->nullable();
            $table->timestamps();
            $table->index(['assigned_to', 'starts_at', 'ends_at']);
        });

        Schema::create('quotations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->uuid('revision_group');
            $table->unsignedSmallInteger('revision_number');
            $table->char('currency', 3);
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('charges_amount', 14, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->decimal('deposit_percent', 5, 2)->nullable();
            $table->text('terms')->nullable();
            $table->text('exclusions')->nullable();
            $table->string('status', 24)->default('draft')->index();
            $table->string('acceptance_token_hash', 64)->nullable()->unique();
            $table->timestamp('acceptance_expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['revision_group', 'revision_number']);
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('quotation_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->foreignId('source_content_entry_id')->nullable()->constrained('content_entries')->nullOnDelete();
            $table->string('source_title_snapshot')->nullable();
            $table->timestamps();
            $table->index(['quotation_id', 'position']);
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('number', 32)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->char('currency', 3);
            $table->date('issued_at')->index();
            $table->date('due_at')->nullable()->index();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->string('status', 24)->default('draft')->index();
            $table->text('terms')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'issued_at']);
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
            $table->index(['invoice_id', 'position']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->string('method', 40);
            $table->string('transaction_reference')->nullable()->unique();
            $table->timestamp('paid_at');
            $table->string('status', 24)->default('recorded')->index();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['invoice_id', 'paid_at']);
        });

        Schema::create('financial_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 24);
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3);
            $table->string('reference', 80)->nullable()->unique();
            $table->text('reason');
            $table->string('status', 24)->default('recorded');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_adjustments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('appointments');
        Schema::table('follow_up_tasks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('event_id');
        });
        Schema::dropIfExists('events');
    }
};
