<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
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
        Storage::fake('ebooks');
        $this->actingAs(User::factory()->create());

        foreach (config('ebooks') as $key => $book) {
            $bytes = "%PDF-1.4\nTest fixture for ".$key."\n%%EOF\n";
            Storage::disk('ebooks')->put($book['file'], $bytes);
            $response = $this->get(route('ebooks.show', $key))
                ->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
            $this->assertSame($bytes, $response->streamedContent());
        }

        $this->get('/ebooks/not-a-book')->assertNotFound();
    }

    public function test_missing_authorized_file_returns_not_found(): void
    {
        Storage::fake('ebooks');
        Gate::define('access-paid-ebooks', fn (User $user) => true);
        $this->actingAs(User::factory()->create())
            ->get(route('ebooks.show', array_key_first(config('ebooks'))))->assertNotFound();
    }
}
