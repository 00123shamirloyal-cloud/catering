<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_homepage_loads_with_a_booking_call_to_action(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Plan your event');
    }
}
