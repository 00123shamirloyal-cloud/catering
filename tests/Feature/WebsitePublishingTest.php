<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\ContentEntryVersion;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class WebsitePublishingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_content_editor_cannot_publish_a_website_entry(): void
    {
        $editor = User::factory()->create(['role' => 'content_editor']);
        $entry = ContentEntry::factory()->create(['type' => 'service', 'slug' => 'editor-draft']);

        $this->actingAs($editor)->post(route('admin.content.publish', $entry))->assertForbidden();
        $this->assertDatabaseHas('content_entries', ['id' => $entry->id, 'status' => 'draft']);
    }

    public function test_owner_can_publish_an_entry_and_it_appears_on_the_public_site(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $entry = ContentEntry::factory()->create([
            'type' => 'service',
            'title' => 'Seasonal celebration catering',
            'slug' => 'seasonal-celebration-catering',
        ]);

        $this->actingAs($owner)->post(route('admin.content.publish', $entry))->assertRedirect();

        $this->assertDatabaseHas('content_entries', ['id' => $entry->id, 'status' => 'published']);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => ContentEntry::class, 'subject_id' => $entry->id]);
        $this->get(route('services.show', $entry->slug))->assertOk()->assertSee('Seasonal celebration catering');
    }

    public function test_public_service_detail_escapes_content_instead_of_rendering_html(): void
    {
        $entry = ContentEntry::factory()->published()->create([
            'type' => 'service',
            'title' => 'Safe service title',
            'slug' => 'safe-service-title',
            'body' => '<script>alert("xss")</script>',
        ]);

        $response = $this->get(route('services.show', $entry->slug))->assertOk();

        $response->assertSee('&lt;script&gt;', false);
        $response->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_public_homepage_uses_daata_branding_and_a_city_level_map_link(): void
    {
        SiteSetting::query()->create(['key' => 'business_name', 'value' => 'Daata Catering and Event Organizer']);
        SiteSetting::query()->create(['key' => 'city', 'value' => 'Rawalpindi']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Daata Catering and Event Organizer')
            ->assertSee('/images/daata-logo-placeholder.svg', false)
            ->assertSee('Rawalpindi, Pakistan · Open city map')
            ->assertDontSee('Message on WhatsApp');
    }

    public function test_contact_page_hides_unverified_contact_channels(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('Business phone can be added')
            ->assertDontSee('Business email can be added')
            ->assertDontSee('Message us on WhatsApp');
    }

    public function test_content_editor_can_restore_a_saved_version_but_it_returns_for_owner_approval(): void
    {
        $editor = User::factory()->create(['role' => 'content_editor']);
        $entry = ContentEntry::factory()->published()->create([
            'type' => 'service',
            'title' => 'Original service',
            'slug' => 'original-service',
            'excerpt' => 'Original description',
            'body' => 'Original content body.',
        ]);

        $this->actingAs($editor)
            ->put(route('admin.content.update', $entry), [
                'type' => 'service',
                'title' => 'Updated service',
                'slug' => 'original-service',
                'category' => 'Celebrations',
                'excerpt' => 'Updated description',
                'body' => 'Updated content body.',
            ])
            ->assertRedirect(route('admin.content.index'));

        $version = ContentEntryVersion::query()->where('content_entry_id', $entry->id)->firstOrFail();
        $this->assertSame('Original service', $version->snapshot['title']);

        $this->post(route('admin.content.versions.restore', [$entry, $version]))
            ->assertRedirect(route('admin.content.versions', $entry));

        $this->assertDatabaseHas('content_entries', [
            'id' => $entry->id,
            'title' => 'Original service',
            'body' => 'Original content body.',
            'status' => 'pending_approval',
            'published_at' => null,
        ]);
        $this->get(route('services.show', 'original-service'))->assertNotFound();
    }
}
