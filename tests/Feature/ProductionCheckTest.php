<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
