<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicInquiryWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_published_services_are_visible_and_draft_services_stay_private(): void
    {
        $published = ContentEntry::factory()->published()->create([
            'type' => 'service',
            'title' => 'Garden wedding catering',
            'slug' => 'garden-wedding-catering',
        ]);
        ContentEntry::factory()->create([
            'type' => 'service',
            'title' => 'Private tasting service',
            'slug' => 'private-tasting-service',
        ]);

        $this->get(route('services.show', $published->slug))->assertOk()->assertSee('Garden wedding catering');
        $this->get(route('services.show', 'private-tasting-service'))->assertNotFound();
    }

    public function test_inquiry_submission_creates_customer_follow_up_and_audit_record(): void
    {
        $service = ContentEntry::factory()->published()->create([
            'type' => 'service',
            'title' => 'Wedding catering',
            'slug' => 'wedding-catering',
        ]);
        $payload = $this->validInquiryPayload([$service->id]);

        $response = $this->post(route('booking.store'), $payload);
        $inquiry = Inquiry::query()->where('idempotency_token', $payload['idempotency_token'])->firstOrFail();

        $response->assertRedirect(route('booking.confirmation', $inquiry->reference));
        $this->assertDatabaseHas('inquiries', ['reference' => $inquiry->reference, 'status' => 'new']);
        $this->assertDatabaseHas('customers', ['name' => 'Avery Sample', 'email' => 'avery@example.test']);
        $this->assertDatabaseHas('follow_up_tasks', ['inquiry_id' => $inquiry->id, 'status' => 'open']);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => Inquiry::class, 'subject_id' => $inquiry->id]);
        $this->assertDatabaseHas('inquiry_content_entry', ['inquiry_id' => $inquiry->id, 'content_entry_id' => $service->id]);

        $this->get(route('booking.confirmation', $inquiry->reference))
            ->assertOk()
            ->assertSee($inquiry->reference)
            ->assertSee('not a confirmed booking');
    }

    public function test_repeated_submission_with_the_same_token_does_not_create_duplicate_records(): void
    {
        $payload = $this->validInquiryPayload();

        $firstResponse = $this->post(route('booking.store'), $payload);
        $secondResponse = $this->post(route('booking.store'), $payload);

        $firstResponse->assertRedirect();
        $secondResponse->assertRedirect($firstResponse->headers->get('Location'));
        $this->assertDatabaseCount('inquiries', 1);
        $this->assertDatabaseCount('customers', 1);
        $this->assertDatabaseCount('follow_up_tasks', 1);
    }

    public function test_inquiry_without_privacy_acknowledgement_is_rejected(): void
    {
        $payload = $this->validInquiryPayload();
        unset($payload['privacy_accepted']);

        $this->from(route('booking.create'))
            ->post(route('booking.store'), $payload)
            ->assertRedirect(route('booking.create'))
            ->assertSessionHasErrors(['privacy_accepted']);

        $this->assertDatabaseCount('inquiries', 0);
        $this->assertDatabaseCount('follow_up_tasks', 0);
    }

    /** @param array<int, int> $serviceIds */
    private function validInquiryPayload(array $serviceIds = []): array
    {
        return [
            'full_name' => 'Avery Sample',
            'email' => 'avery@example.test',
            'phone' => '+923001234567',
            'preferred_contact' => 'email',
            'event_type' => 'Wedding',
            'event_date' => now()->addMonths(3)->toDateString(),
            'guest_count' => 48,
            'city' => 'Lahore',
            'setting' => 'outdoor',
            'service_ids' => $serviceIds,
            'privacy_accepted' => '1',
            'idempotency_token' => (string) Str::uuid(),
        ];
    }
}
