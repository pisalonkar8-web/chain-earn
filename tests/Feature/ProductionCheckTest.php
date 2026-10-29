<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_configuration_is_not_reported_as_production_ready(): void
    {
        config(['app.env' => 'testing', 'mail.default' => 'log', 'session.secure' => false]);
        $this->artisan('chain:check-production')
            ->expectsOutputToContain('FAIL')
            ->expectsOutputToContain('SMTP delivery')
            ->assertExitCode(1);
    }

    public function test_preflight_rejects_malformed_key_and_placeholder_url_and_unsafe_cookies(): void
    {
        config([
            'app.key' => 'base64:not-a-key', 'app.url' => 'https://REPLACE_ME',
            'session.http_only' => false, 'session.same_site' => 'none', 'simulation.enabled' => true,
        ]);
        $this->assertSame(1, Artisan::call('chain:check-production'));
        $output = Artisan::output();
        foreach (['Application key valid for cipher', 'HTTPS application URL', 'HTTP-only session cookie',
            'SameSite session protection', 'Simulation disabled explicitly'] as $label) {
            $this->assertMatchesRegularExpression('/'.preg_quote($label, '/').'\s*\| FAIL\s*\|/', $output);
        }
    }
}
