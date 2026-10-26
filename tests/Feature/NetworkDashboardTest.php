<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NetworkDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function member(?User $sponsor = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $profile = $user->profile()->make(['referral_code' => (string) Str::uuid()]);
        $profile->sponsor_id = $sponsor?->id;
        $profile->save();

        return $user;
    }

    public function test_network_counts_are_scoped_to_four_descendant_levels(): void
    {
        $this->withoutVite();
        $owner = $this->member();
        $parent = $owner;
        for ($i = 1; $i <= 5; $i++) {
            $parent = $this->member($parent);
        }
        $this->member($owner);
        $this->member($this->member());
        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertViewHas('levelCounts', [1 => 2, 2 => 1, 3 => 1, 4 => 1]);
    }

    public function test_members_only_see_their_direct_referrals_without_contact_details(): void
    {
        $this->withoutVite();
        $owner = $this->member();
        $direct = $this->member($owner, ['name' => 'Direct Visible Member']);
        $this->member($direct, ['name' => 'Private Indirect Member']);
        $this->member(null, ['name' => 'Unrelated Private Member']);
        $this->actingAs($owner)->get('/dashboard')->assertOk()
            ->assertSee('Direct Visible Member')->assertDontSee($direct->email)
            ->assertDontSee('Private Indirect Member')->assertDontSee('Unrelated Private Member');
    }

    public function test_network_empty_state_and_legacy_member_without_profile(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertSee('No direct referrals yet')->assertSee('Your profile has not been created yet.')
            ->assertViewHas('levelCounts', [1 => 0, 2 => 0, 3 => 0, 4 => 0]);
    }

    public function test_direct_referrals_are_paginated(): void
    {
        $this->withoutVite();
        $owner = $this->member();
        for ($i = 0; $i < 16; $i++) {
            $this->member($owner);
        }
        $this->actingAs($owner)->get('/dashboard?page=2')->assertOk()
            ->assertViewHas('directMembers', fn ($members) => $members->total() === 16 && $members->count() === 1);
    }
}
