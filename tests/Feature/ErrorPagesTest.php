<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_error_pages_preserve_status_without_exposing_internal_details(): void
    {
        $this->withoutVite();
        config(['app.debug' => false]);
        Route::get('/_test-error/{status}', fn (int $status) => abort($status, 'Private internal details'));

        foreach ([403, 404, 419, 429, 500, 503] as $status) {
            $this->get('/_test-error/'.$status)->assertStatus($status)
                ->assertSee('Return home')->assertDontSee('Private internal details');
        }
    }
}
