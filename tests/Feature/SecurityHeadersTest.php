<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_send_browser_protection_headers(): void
    {
        $this->withoutVite();
        $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_dashboard_and_reset_links_cannot_be_cached(): void
    {
        $this->withoutVite();
        $this->get('/reset-password/private-token')->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
