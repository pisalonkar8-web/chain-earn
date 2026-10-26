<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        // Indian mobile numbers: accept spaces/hyphens and optional +91/91.
        $phone = $request->input('phone');
        if (is_string($phone)) {
            $phone = preg_replace('/[\s()-]+/', '', $phone);
        }
        if (is_string($phone) && preg_match('/^(?:\+91|91)?([6-9][0-9]{9})$/', $phone, $matches)) {
            $phone = '+91'.$matches[1];
        }
        $referral = $request->input('referral_code');
        $request->merge(['phone' => $phone, 'referral_code' => is_string($referral) ? trim($referral) : $referral]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'phone' => ['required', 'string', 'regex:/^\+91[6-9][0-9]{9}$/'],
            'referral_code' => ['nullable', 'string', 'uuid', 'exists:profiles,referral_code'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $sponsor = empty($data['referral_code']) ? null : Profile::where('referral_code', $data['referral_code'])->lockForUpdate()->first();
            if (! empty($data['referral_code']) && ! $sponsor) {
                throw ValidationException::withMessages(['referral_code' => 'This referral invitation is no longer available.']);
            }
            $user = User::create(\Illuminate\Support\Arr::only($data, ['name', 'email', 'password']));
            $profile = new Profile(['referral_code' => (string) Str::uuid(), 'phone' => $data['phone']]);
            $profile->sponsor_id = $sponsor?->user_id;
            $user->profile()->save($profile);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        event(new Registered($user));

        return redirect()->route('verification.notice');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
