<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_staff_sign_in(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_non_staff_account_cannot_open_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_staff_login_uses_the_daata_brand_name_and_placeholder_mark(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Daata Catering and Event Organizer')
            ->assertSee('/images/daata-logo-placeholder.svg', false);
    }

    public function test_owner_can_view_dashboard_with_database_backed_empty_state(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No inquiries in this date range');
    }

    public function test_inactive_staff_cannot_sign_in(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => bcrypt('change-this-password'),
            'role' => 'manager',
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'change-this-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_only_owner_can_manage_staff_accounts(): void
    {
        $finance = User::factory()->create(['role' => 'finance']);
        $this->actingAs($finance)->get(route('admin.staff.index'))->assertForbidden();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner)->get(route('admin.staff.index'))->assertOk()->assertSee('Team access');
    }

    public function test_content_editor_cannot_access_crm_records_or_search(): void
    {
        $editor = User::factory()->create(['role' => 'content_editor']);
        Inquiry::factory()->create(['customer_name' => 'Private CRM Record']);

        $this->actingAs($editor)
            ->get(route('admin.inquiries.index'))
            ->assertForbidden();

        $this->get(route('admin.search', ['q' => 'Private CRM Record']))
            ->assertForbidden();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Private CRM Record')
            ->assertDontSee('Review inquiries');
    }

    public function test_owner_can_open_reports_and_export_a_date_filtered_csv(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $this->actingAs($owner)->get(route('admin.reports.index', ['start' => $start, 'end' => $end]))
            ->assertOk()
            ->assertSee('Business performance');

        $export = $this->get(route('admin.reports.export', ['start' => $start, 'end' => $end]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertStringContainsString('Total inquiries', $export->streamedContent());
    }
}
