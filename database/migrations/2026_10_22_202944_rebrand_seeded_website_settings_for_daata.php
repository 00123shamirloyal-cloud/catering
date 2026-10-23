<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'business_name' => ['Gather & Grain', 'Daata Catering and Event Organizer'],
            'tagline' => ['Food, thoughtfully brought together.', 'Catering and event planning for celebrations in Rawalpindi.'],
            'hero_title' => ['Make room for a beautiful gathering.', 'Bring your celebration together.'],
            'hero_subtitle' => [
                'Seasonal catering and considered event details, shaped around the way you want to celebrate.',
                'Daata Catering and Event Organizer is based in Rawalpindi, Pakistan. Share your occasion, guest count and venue so we can discuss a suitable menu and event plan.',
            ],
            'demo_notice' => [
                'Sample content — update with your company details before launch.',
                'Starter content — confirm services, menus, contact details and policies before launch.',
            ],
            'city' => ['', 'Rawalpindi'],
        ] as $key => [$oldValue, $newValue]) {
            $this->replaceSetting($key, $oldValue, $newValue);
        }
    }

    public function down(): void
    {
        foreach ([
            'business_name' => ['Daata Catering and Event Organizer', 'Gather & Grain'],
            'tagline' => ['Catering and event planning for celebrations in Rawalpindi.', 'Food, thoughtfully brought together.'],
            'hero_title' => ['Bring your celebration together.', 'Make room for a beautiful gathering.'],
            'hero_subtitle' => [
                'Daata Catering and Event Organizer is based in Rawalpindi, Pakistan. Share your occasion, guest count and venue so we can discuss a suitable menu and event plan.',
                'Seasonal catering and considered event details, shaped around the way you want to celebrate.',
            ],
            'demo_notice' => [
                'Starter content — confirm services, menus, contact details and policies before launch.',
                'Sample content — update with your company details before launch.',
            ],
            'city' => ['Rawalpindi', ''],
        ] as $key => [$currentValue, $restoredValue]) {
            $this->replaceSetting($key, $currentValue, $restoredValue);
        }
    }

    private function replaceSetting(string $key, string $oldValue, string $newValue): void
    {
        $row = DB::table('site_settings')->where('key', $key)->first(['value']);

        if (! $row || json_decode($row->value, true) !== $oldValue) {
            return;
        }

        DB::table('site_settings')->where('key', $key)->update([
            'value' => json_encode($newValue, JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }
};
