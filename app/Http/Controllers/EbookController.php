<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EbookController extends Controller
{
    public function show(string $ebook)
    {
        $book = config('ebooks')[$ebook] ?? null;
        abort_unless($book, 404);

        // Fail closed: no production paid entitlement exists in this preview.
        // Connect this ability to verified payment-backed access before launch.
        // Never authorize using sim_purchases or registration alone.
        Gate::authorize('access-paid-ebooks');

        $disk = Storage::disk('ebooks');
        abort_unless($disk->exists($book['file']), 404);

        return $disk->response($book['file'], $book['file'], [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}
