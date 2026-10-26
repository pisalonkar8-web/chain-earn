<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Simulation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SimulationTest extends TestCase
{
    use RefreshDatabase;

    private function member(?User $sponsor = null): User
    {
        $user = User::factory()->create();
        $profile = $user->profile()->make(['referral_code' => (string) Str::uuid()]);
        $profile->sponsor_id = $sponsor?->id;
        $profile->save();

        return $user;
    }

    private function service(): Simulation
    {
        return app(Simulation::class);
    }

    private function admin(string $role = 'super'): User
    {
        $user = $this->member();
        DB::table('sim_access')->insert(['user_id' => $user->id, 'role' => $role]);

        return $user;
    }

    private function earned(): User
    {
        $owner = $this->member();
        $this->service()->purchase($owner);
        $this->service()->purchase($this->member($owner));

        return $owner;
    }

    public function test_four_levels_pay_exactly_once_and_do_not_pay_a_fifth_level(): void
    {
        $chain = [$this->member()];
        for ($i = 1; $i <= 5; $i++) {
            $chain[] = $this->member($chain[$i - 1]);
        }
        foreach (array_slice($chain, 0, 5) as $member) {
            $this->service()->purchase($member);
        }
        $id = $this->service()->purchase($chain[5]);
        $this->assertSame($id, $this->service()->purchase($chain[5]));
        $entries = DB::table('sim_ledger')->where('purchase_id', $id)->orderBy('level')->get();
        $this->assertSame([50000, 25000, 10000, 5000], $entries->pluck('amount')->all());
        $this->assertSame([$chain[4]->id, $chain[3]->id, $chain[2]->id, $chain[1]->id], $entries->pluck('user_id')->all());
    }

    public function test_ineligible_level_is_skipped_without_redistribution_or_retroactive_credit(): void
    {
        $grandparent = $this->member();
        $parent = $this->member($grandparent);
        $buyer = $this->member($parent);
        $this->service()->purchase($grandparent);
        $id = $this->service()->purchase($buyer);
        $this->assertSame(0, $this->service()->balance($parent->id));
        $this->assertSame(25000, $this->service()->balance($grandparent->id));
        $this->service()->purchase($parent);
        $this->service()->purchase($buyer);
        $this->assertSame(1, DB::table('sim_ledger')->where('purchase_id', $id)->count());
    }

    public function test_withdrawal_reservation_is_idempotent_and_cannot_overspend(): void
    {
        $owner = $this->earned();
        $key = (string) Str::uuid();
        $id = $this->service()->withdraw($owner, 50000, $key);
        $this->assertSame($id, $this->service()->withdraw($owner, 50000, $key));
        $this->assertSame(0, $this->service()->balance($owner->id));
        $this->expectException(ValidationException::class);
        $this->service()->withdraw($owner, 50000, (string) Str::uuid());
    }

    public function test_minimum_withdrawal_is_enforced(): void
    {
        $this->expectException(ValidationException::class);
        $this->service()->withdraw($this->earned(), 49999, (string) Str::uuid());
    }

    public function test_request_key_cannot_be_reused_for_another_account(): void
    {
        $key = (string) Str::uuid();
        $this->service()->withdraw($this->earned(), 50000, $key);
        $this->expectException(HttpException::class);
        $this->service()->withdraw($this->earned(), 50000, $key);
    }

    public function test_rejection_releases_balance_once_and_cannot_later_complete(): void
    {
        $owner = $this->earned();
        $admin = $this->admin('reviewer');
        $id = $this->service()->withdraw($owner, 50000, (string) Str::uuid());
        $this->service()->review($admin, $id, 'reject', 'Demo rejection test');
        $this->service()->review($admin, $id, 'reject', 'Repeated submission');
        $this->assertSame(50000, $this->service()->balance($owner->id));
        $this->assertSame(1, DB::table('sim_ledger')->where('reference', "withdrawal:$id:release")->count());
        $this->expectException(HttpException::class);
        $this->service()->review($admin, $id, 'complete', 'Invalid transition');
    }

    public function test_approval_then_failure_releases_reserved_balance(): void
    {
        $owner = $this->earned();
        $admin = $this->admin();
        $id = $this->service()->withdraw($owner, 50000, (string) Str::uuid());
        $this->service()->review($admin, $id, 'approve', 'Demo approval');
        $this->assertSame(0, $this->service()->balance($owner->id));
        $this->service()->review($admin, $id, 'fail', 'Simulated provider failure');
        $this->assertSame(50000, $this->service()->balance($owner->id));
    }

    public function test_completed_simulated_payout_stays_debited_and_is_audited(): void
    {
        $owner = $this->earned();
        $admin = $this->admin();
        $id = $this->service()->withdraw($owner, 50000, (string) Str::uuid());
        $this->service()->review($admin, $id, 'approve', 'Demo approval');
        $this->service()->review($admin, $id, 'complete', 'Demo completion');
        $this->service()->review($admin, $id, 'complete', 'Repeated completion');
        $this->assertSame(0, $this->service()->balance($owner->id));
        $this->assertDatabaseHas('sim_withdrawals', ['id' => $id, 'status' => 'simulated_paid']);
        $this->assertSame(1, DB::table('sim_audit')->where('action', 'demo_withdrawal_complete')->count());
    }

    public function test_refund_reverses_rewards_once_and_blocks_approval_of_reserved_debt(): void
    {
        $owner = $this->earned();
        $purchase = DB::table('sim_purchases')->latest('id')->first();
        $admin = $this->admin();
        $id = $this->service()->withdraw($owner, 50000, (string) Str::uuid());
        $this->service()->refund($admin, $purchase->id, 'Demo purchase refund');
        $this->service()->refund($admin, $purchase->id, 'Repeated refund');
        $this->assertSame(-50000, $this->service()->balance($owner->id));
        $this->assertDatabaseHas('sim_purchases', ['id' => $purchase->id, 'status' => 'refunded']);
        $this->assertSame(1, DB::table('sim_ledger')->where('type', 'commission_reversal')->count());
        $this->expectException(ValidationException::class);
        $this->service()->review($admin, $id, 'approve', 'Should be blocked');
    }

    public function test_http_authorization_and_private_wallet_scoping(): void
    {
        $this->withoutVite();
        $this->get('/simulation')->assertRedirect('/login');
        $unverified = User::factory()->unverified()->create();
        $this->actingAs($unverified)->get('/simulation')->assertRedirect('/verify-email');
        $owner = $this->earned();
        $stranger = $this->member();
        $this->actingAs($stranger)->get('/simulation')->assertOk()->assertViewHas('balance', 0)
            ->assertSee('PDF will be provided later')->assertDontSee($owner->email);
        $this->get('/simulation/admin')->assertForbidden();
        $this->post('/simulation/admin/purchases/1/refund', ['reason' => 'Unauthorized refund'])->assertForbidden();
        $this->actingAs($this->admin('reviewer'))->get('/simulation/admin')->assertOk()->assertDontSee('Demo audit log');
        $this->post('/simulation/admin/purchases/1/refund', ['reason' => 'Staff cannot refund'])->assertForbidden();
        $this->actingAs($this->admin())->get('/simulation/admin')->assertOk()->assertSee('Demo audit log');
    }

    public function test_self_review_is_forbidden_and_reason_is_required(): void
    {
        $this->withoutVite();
        $owner = $this->earned();
        DB::table('sim_access')->insert(['user_id' => $owner->id, 'role' => 'super']);
        $id = $this->service()->withdraw($owner, 50000, (string) Str::uuid());
        $this->actingAs($owner)->post("/simulation/admin/withdrawals/$id", ['action' => 'approve'])->assertSessionHasErrors('reason');
        $this->post("/simulation/admin/withdrawals/$id", ['action' => 'approve', 'reason' => 'Self review attempt'])->assertForbidden();
        $this->assertDatabaseHas('sim_withdrawals', ['id' => $id, 'status' => 'pending']);
    }

    public function test_simulation_is_unavailable_in_production_or_when_disabled(): void
    {
        $this->withoutVite();
        $this->actingAs($this->member());
        config(['simulation.enabled' => false]);
        $this->get('/simulation')->assertNotFound();
        $this->post('/simulation/purchase', ['simulation_acknowledged' => 1])->assertNotFound();
        config(['simulation.enabled' => true]);
        app()->instance('env', 'production');
        $this->get('/simulation')->assertNotFound();
        $this->withSession(['_token' => 'production-guard-test'])
            ->post('/simulation/purchase', ['simulation_acknowledged' => 1, '_token' => 'production-guard-test'])->assertNotFound();
        $this->assertDatabaseCount('sim_purchases', 0);
    }

    public function test_purchase_requires_explicit_simulation_acknowledgement(): void
    {
        $this->actingAs($this->member())->post('/simulation/purchase')->assertSessionHasErrors('simulation_acknowledged');
        $this->assertDatabaseCount('sim_purchases', 0);
    }
}
