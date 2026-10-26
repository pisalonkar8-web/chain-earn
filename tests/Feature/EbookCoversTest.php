<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EbookCoversTest extends TestCase
{
    use RefreshDatabase;

    public function test_covers_link_to_their_protected_pdfs_and_access_is_denied_by_default(): void
    {
        $this->withoutVite();
        $user = User::factory()->create();
        $home = $this->get('/')->assertOk();

        foreach (config('ebooks') as $key => $book) {
            $home->assertSee(route('ebooks.show', $key), false)
                ->assertSee(asset('images/ebooks/'.$book['cover']), false);
            $this->assertFileExists(public_path('images/ebooks/'.$book['cover']));
            $this->get(route('ebooks.show', $key))->assertRedirect(route('login'));
            $this->actingAs($user)->get(route('ebooks.show', $key))->assertForbidden();
            auth()->forgetGuards();
        }
    }

    public function test_authorized_delivery_returns_the_exact_mapped_pdf_inline(): void
    {
        // Test the protected file mapping only. This does not simulate a payment.
        Gate::define('access-paid-ebooks', fn (User $user) => true);
        $this->actingAs(User::factory()->create());

        foreach (config('ebooks') as $key => $book) {
            $path = storage_path('app/private/ebooks/'.$book['file']);
            $this->assertFileExists($path);
            $response = $this->get(route('ebooks.show', $key))
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame(hash_file('sha256', $path), hash('sha256', $response->streamedContent()));
        }

        $this->get('/ebooks/not-a-book')->assertNotFound();
    }
}
