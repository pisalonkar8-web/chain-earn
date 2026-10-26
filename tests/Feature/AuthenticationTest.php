<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        \Illuminate\Support\Facades\Notification::fake();
    }

    public function test_guests_cannot_access_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_registration_creates_a_profile_and_hashes_the_password(): void
    {
        $this->post('/register', [
            'name' => 'New Member', 'phone' => '98765 43210',
            'email' => 'member@example.com',
            'password' => 'example-password-123',
            'password_confirmation' => 'example-password-123',
        ])->assertRedirect('/verify-email');

        $user = User::where('email', 'member@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('example-password-123', $user->password));
        $this->assertNotNull($user->profile);
        $this->assertNotEmpty($user->profile->referral_code);
        $this->get('/dashboard')->assertRedirect('/verify-email');
        $user->markEmailAsVerified();
        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Demo data')->assertSee($user->profile->referral_code);
    }

    public function test_invalid_registration_and_duplicate_email_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->post('/register', [
            'name' => 'Another Member', 'email' => $user->email,
            'password' => 'short', 'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('profiles', 0);
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'example-password-123']);
        $this->post('/login', ['email' => $user->email, 'password' => 'example-password-123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_incorrect_password_does_not_authenticate(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requests_are_rate_limited(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', ['email' => 'missing@example.com', 'password' => 'incorrect']);
        }
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'incorrect'])->assertStatus(429);
    }

    public function test_deleting_a_user_cascades_to_the_profile(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['referral_code' => (string) \Illuminate\Support\Str::uuid()]);
        $user->delete();
        $this->assertDatabaseCount('profiles', 0);
    }
}
