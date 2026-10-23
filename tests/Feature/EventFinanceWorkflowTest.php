<?php

namespace Tests\Feature;

use App\Actions\BuildQuotation;
use App\Actions\CreateEventFromInquiry;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Inquiry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventFinanceWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_overlapping_appointments_for_one_employee_are_rejected(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);
        $startsAt = now()->addDays(3)->setTime(14, 0);

        Appointment::factory()->create([
            'assigned_to' => $manager->id,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addHour(),
        ]);

        $this->actingAs($manager)
            ->from(route('admin.appointments.create'))
            ->post(route('admin.appointments.store'), [
                'title' => 'Menu tasting',
                'type' => 'menu_tasting',
                'assigned_to' => $manager->id,
                'starts_at' => $startsAt->copy()->addMinutes(30)->format('Y-m-d\TH:i'),
                'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d\TH:i'),
                'timezone' => config('app.timezone'),
            ])
            ->assertRedirect(route('admin.appointments.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_a_tentative_hold_cannot_be_confirmed_without_an_accepted_quotation(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $inquiry = Inquiry::factory()->create();
        $event = app(CreateEventFromInquiry::class)->handle($inquiry, [
            'title' => 'Avery’s wedding',
            'category' => 'Wedding',
            'event_date' => now()->addMonths(2)->toDateString(),
            'start_time' => '17:00',
            'end_time' => '22:00',
            'venue' => 'Garden Hall',
            'city' => 'Lahore',
            'guest_count' => 80,
        ], $owner->id);

        $this->actingAs($owner)
            ->put(route('admin.events.status', $event), ['status' => 'confirmed'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'tentative_hold']);
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'qualified']);
    }

    public function test_a_tentative_event_can_be_confirmed_after_its_quote_is_accepted(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $inquiry = Inquiry::factory()->create();
        $event = app(CreateEventFromInquiry::class)->handle($inquiry, [
            'title' => 'Avery’s wedding',
            'category' => 'Wedding',
            'event_date' => now()->addMonths(2)->toDateString(),
            'start_time' => '17:00',
            'end_time' => '22:00',
            'venue' => 'Garden Hall',
        ], $owner->id);
        $quotation = Quotation::factory()->create([
            'customer_id' => $event->customer_id,
            'event_id' => $event->id,
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->actingAs($owner)
            ->put(route('admin.events.status', $event), ['status' => 'confirmed'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'confirmed']);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => Event::class, 'subject_id' => $event->id]);
    }

    public function test_quotation_totals_and_revision_snapshots_are_preserved(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $inquiry = Inquiry::factory()->create();
        $action = app(BuildQuotation::class);
        $first = $action->handle([
            'inquiry_id' => $inquiry->id,
            'currency' => 'PKR',
            'items' => [
                ['description' => 'Dinner service', 'quantity' => '2', 'unit_price' => '100'],
                ['description' => 'Table styling', 'quantity' => '3', 'unit_price' => '50'],
            ],
            'discount_amount' => '25',
            'charges_amount' => '10',
            'tax_rate' => '10',
        ], $owner->id);

        $this->assertSame('350.00', $first->subtotal);
        $this->assertSame('33.50', $first->tax_amount);
        $this->assertSame('368.50', $first->total);

        $second = $action->handle([
            'revision_of' => $first->id,
            'currency' => 'PKR',
            'items' => [
                ['description' => 'Revised dinner service', 'quantity' => '1', 'unit_price' => '400'],
            ],
            'tax_rate' => '0',
        ], $owner->id);

        $this->assertSame(2, $second->revision_number);
        $this->assertSame('superseded', $first->fresh()->status);
        $this->assertSame('350.00', $first->fresh()->subtotal);
        $this->assertSame('Dinner service', $first->items()->first()->description);
        $this->assertSame('400.00', $second->total);
    }

    public function test_public_proposal_acceptance_is_tokenized_and_does_not_confirm_the_event(): void
    {
        $customer = Customer::factory()->create();
        $event = Event::factory()->create(['customer_id' => $customer->id]);
        $token = Str::random(64);
        $quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'status' => 'sent',
            'acceptance_token_hash' => hash('sha256', $token),
            'acceptance_expires_at' => now()->addDays(4),
        ]);

        $this->get(route('proposals.show', [$quotation, $token]))
            ->assertOk()
            ->assertSee($customer->name);

        $pdf = $this->get(route('proposals.pdf', [$quotation, $token]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertStringStartsWith('%PDF-', $pdf->getContent());

        $this->post(route('proposals.accept', [$quotation, $token]), ['accept_terms' => '1'])
            ->assertOk()
            ->assertSee('does not itself confirm the event booking');

        $this->assertDatabaseHas('quotations', ['id' => $quotation->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'status' => 'tentative_hold']);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => Quotation::class, 'subject_id' => $quotation->id]);
    }

    public function test_offline_payments_update_invoice_balance_and_are_idempotent_by_reference(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $invoice = Invoice::factory()->create(['currency' => 'PKR', 'total' => '1000.00', 'status' => 'unpaid']);
        $payload = [
            'amount' => '400.00',
            'method' => 'bank_transfer',
            'transaction_reference' => 'BANK-REF-400',
            'paid_at' => now()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($finance)
            ->post(route('admin.invoices.payments.store', $invoice), $payload)
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $this->post(route('admin.invoices.payments.store', $invoice), $payload)
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertDatabaseCount('payments', 1);
        $this->assertSame('600.00', $invoice->fresh()->balanceDue());
        $this->assertSame('partially_paid', $invoice->fresh()->status);

        $this->post(route('admin.invoices.payments.store', $invoice), [
            ...$payload,
            'amount' => '600.00',
            'transaction_reference' => 'BANK-REF-600',
        ])->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertSame('0.00', $invoice->fresh()->balanceDue());
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_refunds_are_records_that_cannot_exceed_the_original_payment(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $invoice = Invoice::factory()->create(['total' => '1000.00', 'status' => 'paid']);
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'currency' => $invoice->currency,
            'amount' => '1000.00',
        ]);
        $payload = [
            'type' => 'refund',
            'payment_id' => $payment->id,
            'amount' => '200.00',
            'reason' => 'Partial service refund approved by the owner.',
            'reference' => 'REFUND-200',
        ];

        $this->actingAs($finance)
            ->post(route('admin.invoices.adjustments.store', $invoice), $payload)
            ->assertRedirect(route('admin.invoices.show', $invoice));

        $this->assertSame('800.00', $invoice->fresh()->amountReceived());
        $this->assertSame('200.00', $invoice->fresh()->balanceDue());
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'recorded']);
        $this->assertDatabaseHas('financial_adjustments', ['invoice_id' => $invoice->id, 'payment_id' => $payment->id, 'type' => 'refund']);

        $this->from(route('admin.invoices.show', $invoice))
            ->post(route('admin.invoices.adjustments.store', $invoice), [...$payload, 'amount' => '900.00', 'reference' => 'REFUND-TOO-MUCH'])
            ->assertRedirect(route('admin.invoices.show', $invoice))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('financial_adjustments', 1);
    }
}
