<?php

return [
    'admin_email' => env('ADMIN_EMAIL', 'owner@example.test'),
    'admin_password' => env('ADMIN_PASSWORD', 'ChangeMe-Local-Only'),
    'event_hold_hours' => (int) env('EVENT_HOLD_HOURS', 48),
    'appointment_types' => ['consultation', 'venue_visit', 'menu_tasting', 'planning_meeting', 'final_briefing'],
];
