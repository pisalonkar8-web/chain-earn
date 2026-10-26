<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Notification::fake();
    }

    private function registration(array $overrides = []): array
    {
        return array_merge(['name' => 'Member', 'email' => 'member@example.com', 'phone' => '98765 43210', 'password' => 'Strong-password-123', 'password_confirmation' => 'Strong-password-123'], $overrides);
    }

    public function test_invited_signup_saves_normalized_phone_and_sponsor_and_sends_verification(): void
    {
        $sponsor = User::factory()->create();
        $profile = $sponsor->profile()->create(['referral_code' => (string) Str::uuid()]);
        $this->post('/register', $this->registration(['referral_code' => $profile->referral_code, 'sponsor_id' => 999]))->assertRedirect('/verify-email');
        $member = User::where('email', 'member@example.com')->firstOrFail();
        $this->assertSame('+919876543210', $member->profile->phone);
        $this->assertEquals($sponsor->id, $member->profile->sponsor_id);
        Notification::assertSentTo($member, VerifyEmail::class);
        $this->get('/dashboard')->assertRedirect('/verify-email');
    }

    public function test_organic_signup_does_not_accept_injected_sponsor(): void
    {
        $this->post('/register', $this->registration(['sponsor_id' => 123]))->assertRedirect('/verify-email');
        $this->assertNull(User::firstOrFail()->profile->sponsor_id);
    }

    public function test_invalid_invitation_and_mobile_create_no_records(): void
    {
        $this->post('/register', $this->registration(['referral_code' => (string) Str::uuid(), 'phone' => '123']))->assertSessionHasErrors(['referral_code', 'phone']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_valid_mobile_does_not_allow_an_unknown_invitation(): void
    {
        $this->post('/register', $this->registration(['referral_code' => (string) Str::uuid()]))->assertSessionHasErrors('referral_code');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_mobile_is_required_and_country_prefix_is_normalized(): void
    {
        $this->post('/register', $this->registration(['phone' => ['invalid'], 'referral_code' => ['invalid']]))->assertSessionHasErrors(['phone', 'referral_code']);
        $this->post('/register', $this->registration(['phone' => '']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->registration(['phone' => '+91 (98765) 43210']))->assertRedirect('/verify-email');
        $this->assertSame('+919876543210', User::firstOrFail()->profile->phone);
    }

    public function test_public_recovery_pages_and_invitation_field_render(): void
    {
        $code = (string) Str::uuid();
        $this->get('/register?ref='.$code)->assertOk()->assertSee($code)->assertSee('Indian mobile number');
        $this->get('/forgot-password')->assertOk()->assertSee('Send reset link');
        $this->get('/reset-password/example-token?email=member@example.com')->assertOk()->assertSee('example-token')->assertSee('member@example.com');
    }

    public function test_a_profile_cannot_sponsor_itself(): void
    {
        $member = User::factory()->create();
        $profile = $member->profile()->make(['referral_code' => (string) Str::uuid()]);
        $profile->sponsor_id = $member->id;
        $this->expectException(\LogicException::class);
        $profile->save();
    }

    public function test_recovery_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/forgot-password', ['email' => 'missing@example.com'])->assertRedirect();
        }
        $this->post('/forgot-password', ['email' => 'missing@example.com'])->assertStatus(429);
    }

    public function test_sponsor_cannot_be_assigned_after_organic_signup(): void
    {
        $member = User::factory()->create();
        $profile = $member->profile()->create(['referral_code' => (string) Str::uuid()]);
        $profile->fill(['sponsor_id' => 123]);
        $this->assertNull($profile->sponsor_id);
        $profile->sponsor_id = User::factory()->create()->id;
        $this->expectException(\LogicException::class);
        $profile->save();
    }

    public function test_sponsor_deletion_is_restricted(): void
    {
        $sponsor = User::factory()->create();
        $member = User::factory()->create();
        $profile = $member->profile()->make(['referral_code' => (string) Str::uuid()]);
        $profile->sponsor_id = $sponsor->id;
        $profile->save();
        $this->expectException(\Illuminate\Database\QueryException::class);
        $sponsor->delete();
    }

    public function test_verification_requires_correct_user_signed_unexpired_link(): void
    {
        $member = User::factory()->unverified()->create();
        $link = (new VerifyEmail)->toMail($member)->actionUrl;
        $this->get($link)->assertRedirect('/login');
        $this->actingAs(User::factory()->unverified()->create())->get($link)->assertForbidden();
        $this->actingAs($member)->get($link.'&tampered=1')->assertForbidden();
        $expired = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $member->id, 'hash' => sha1($member->email)]);
        $this->get($expired)->assertForbidden();
        $this->assertFalse($member->fresh()->hasVerifiedEmail());
        $this->get($link)->assertRedirect('/dashboard');
        $this->assertTrue($member->fresh()->hasVerifiedEmail());
        $this->get('/dashboard')->assertOk();
    }

    public function test_verification_resends_are_throttled(): void
    {
        $member = User::factory()->unverified()->create();
        $this->actingAs($member);
        for ($i = 0; $i < 6; $i++) {
            $this->post('/email/verification-notification')->assertRedirect();
        }
        $this->post('/email/verification-notification')->assertStatus(429);
        Notification::assertSentTo($member, VerifyEmail::class);
    }

    public function test_recovery_response_is_generic_and_notification_contains_valid_token(): void
    {
        $member = User::factory()->create();
        $this->post('/forgot-password', ['email' => $member->email])->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');
        $this->post('/forgot-password', ['email' => 'unknown@example.com'])->assertSessionHas('status', 'If an account exists for that email, a password reset link has been sent.');
        Notification::assertSentTo($member, ResetPassword::class, function ($notification) use ($member) {
            $this->assertStringContainsString('/reset-password/'.$notification->token, $notification->toMail($member)->actionUrl);
            return Password::tokenExists($member, $notification->token);
        });
    }

    public function test_password_reset_revokes_sessions_and_token_cannot_be_reused(): void
    {
        $member = User::factory()->create(['remember_token' => 'old-token']);
        DB::table('sessions')->insert(['id' => 'previous-session', 'user_id' => $member->id, 'payload' => '', 'last_activity' => time()]);
        $token = Password::createToken($member);
        $data = ['email' => $member->email, 'token' => $token, 'password' => 'New-password-456', 'password_confirmation' => 'New-password-456'];
        $this->post('/reset-password', array_merge($data, ['password_confirmation' => 'wrong']))->assertSessionHasErrors('password');
        $this->post('/reset-password', $data)->assertRedirect('/login');
        $this->assertTrue(Hash::check('New-password-456', $member->fresh()->password));
        $this->assertNotSame('old-token', $member->fresh()->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'previous-session']);
        $this->assertGuest();
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
    }

    public function test_expired_and_invalid_reset_tokens_are_rejected(): void
    {
        $member = User::factory()->create();
        $token = Password::createToken($member);
        $data = ['email' => $member->email, 'token' => 'invalid', 'password' => 'New-password-456', 'password_confirmation' => 'New-password-456'];
        $this->post('/reset-password', $data)->assertSessionHasErrors('email');
        $this->travel(61)->minutes();
        $this->post('/reset-password', array_merge($data, ['token' => $token]))->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('New-password-456', $member->fresh()->password));
    }
}
