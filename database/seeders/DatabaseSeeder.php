<?php

namespace Database\Seeders;

use App\Models\ContentEntry;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::query()->firstOrCreate(
            ['email' => config('catering.admin_email')],
            [
                'name' => 'Demo Owner',
                'role' => 'owner',
                'password' => Hash::make((string) config('catering.admin_password')),
            ],
        );

        $settings = [
            'business_name' => 'Daata Catering and Event Organizer',
            'tagline' => 'Catering and event planning for celebrations in Rawalpindi.',
            'hero_title' => 'Bring your celebration together.',
            'hero_subtitle' => 'Daata Catering and Event Organizer is based in Rawalpindi, Pakistan. Share your occasion, guest count and venue so we can discuss a suitable menu and event plan.',
            'phone' => '',
            'email' => '',
            'address' => '',
            'city' => 'Rawalpindi',
            'business_hours' => 'By consultation',
            'whatsapp_url' => '',
            'currency_code' => '',
            'service_areas' => [],
            'show_demo_notice' => true,
            'demo_notice' => 'Starter content — confirm services, menus, contact details and policies before launch.',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $services = [
            ['title' => 'Wedding celebrations', 'slug' => 'wedding-celebrations', 'category' => 'Weddings', 'excerpt' => 'Catering and event-planning enquiries for weddings and family celebrations. Share the date, guest count and venue to start a conversation.', 'body' => 'Starter service description. Confirm menu options, service areas, inclusions and planning details in the website editor before launch.'],
            ['title' => 'Corporate gatherings', 'slug' => 'corporate-gatherings', 'category' => 'Corporate', 'excerpt' => 'For business events and team gatherings, share the event format, guest count, date and venue for a planning discussion.', 'body' => 'Starter service description. Confirm event formats, menu choices and delivery details in the website editor before launch.'],
            ['title' => 'Private celebrations', 'slug' => 'private-celebrations', 'category' => 'Private events', 'excerpt' => 'For birthdays, family occasions and private gatherings, tell us about your event so the team can discuss suitable options.', 'body' => 'Starter service description. Confirm the private event services and inclusions in the website editor before launch.'],
        ];

        foreach ($services as $order => $service) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'service', 'slug' => $service['slug']],
                $service + ['status' => 'published', 'is_featured' => true, 'is_demo' => true, 'sort_order' => $order, 'published_at' => now()],
            );
        }

        $packages = [
            ['title' => 'The Long Table', 'slug' => 'the-long-table', 'category' => 'Weddings', 'excerpt' => 'A flexible family-style starting point for a shared celebration.', 'body' => 'Sample package outline. Final menus, availability, staffing and pricing are confirmed in a tailored quotation.', 'metadata' => ['price_model' => 'quotation', 'price_label' => 'Request a tailored quote']],
            ['title' => 'Gather & Graze', 'slug' => 'gather-and-graze', 'category' => 'Receptions', 'excerpt' => 'A relaxed spread for receptions, launches and informal gatherings.', 'body' => 'Sample package outline. Replace these inclusions and confirm all prices through the owner dashboard.', 'metadata' => ['price_model' => 'quotation', 'price_label' => 'Request a tailored quote']],
        ];

        foreach ($packages as $order => $package) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'package', 'slug' => $package['slug']],
                $package + ['status' => 'published', 'is_featured' => true, 'is_demo' => true, 'sort_order' => $order, 'published_at' => now()],
            );
        }

        $faqs = [
            ['title' => 'How far ahead should I enquire?', 'slug' => 'when-to-enquire', 'body' => 'Share your preferred date as soon as you can. Availability is confirmed by the team after reviewing your event details.'],
            ['title' => 'Can menus be tailored?', 'slug' => 'tailored-menus', 'body' => 'Menu options can be discussed for each event. Dietary and allergen details are confirmed with the team before booking.'],
            ['title' => 'Do you cater at private venues?', 'slug' => 'private-venues', 'body' => 'Add your venue or city to the inquiry and the team can confirm whether the event is within the service area.'],
        ];

        foreach ($faqs as $order => $faq) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'faq', 'slug' => $faq['slug']],
                $faq + ['status' => 'published', 'is_demo' => true, 'sort_order' => $order, 'published_at' => now()],
            );
        }

        $pages = [
            ['title' => 'About us', 'slug' => 'about', 'category' => 'Our story', 'excerpt' => 'Add a short introduction to your company and how you work.', 'body' => 'Replace this sample with your company story, mission, verified experience, team details and service areas. Do not publish claims that have not been confirmed.'],
            ['title' => 'Privacy policy', 'slug' => 'privacy-policy', 'category' => 'Legal', 'excerpt' => 'Owner review required before publication.', 'body' => 'PLACEHOLDER — Add your company-specific privacy practices, data retention details, contact information and applicable legal terms. Have this reviewed before publishing.'],
            ['title' => 'Terms and conditions', 'slug' => 'terms', 'category' => 'Legal', 'excerpt' => 'Owner review required before publication.', 'body' => 'PLACEHOLDER — Add company-specific terms and have them reviewed before publishing.'],
            ['title' => 'Booking policy', 'slug' => 'booking-policy', 'category' => 'Legal', 'excerpt' => 'Owner review required before publication.', 'body' => 'PLACEHOLDER — Explain how proposals, deposits, event confirmation and date holds work for your company. Have the policy reviewed before publishing.'],
            ['title' => 'Cancellation and refund policy', 'slug' => 'cancellation-policy', 'category' => 'Legal', 'excerpt' => 'Owner review required before publication.', 'body' => 'PLACEHOLDER — Add company-specific cancellation, rescheduling and refund terms. Have them reviewed before publishing.'],
        ];

        foreach ($pages as $page) {
            ContentEntry::query()->firstOrCreate(
                ['type' => 'page', 'slug' => $page['slug']],
                $page + ['status' => 'draft', 'is_demo' => true, 'published_at' => null],
            );
        }
    }
}
