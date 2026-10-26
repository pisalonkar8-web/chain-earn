<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Simulation
{
    public static function guard(): void
    {
        abort_unless(app()->environment(['local', 'testing']) && config('simulation.enabled'), 404);
    }

    public function role(int $user): ?string
    {
        return DB::table('sim_access')->where('user_id', $user)->value('role');
    }

    public function balance(int $user): int
    {
        return (int) DB::table('sim_ledger')->where('user_id', $user)->sum('amount');
    }

    private function locked(callable $action): mixed
    {
        self::guard();

        // Serialize demo ledger mutations, including purchases, refunds and reservations.
        return DB::transaction(function () use ($action) {
            abort_unless(DB::table('sim_locks')->where('id', 1)->lockForUpdate()->first(), 503);

            return $action();
        }, 3);
    }

    private function audit(int $actor, string $action, string $subject, string $reason): void
    {
        DB::table('sim_audit')->insert(['actor_id' => $actor, 'action' => $action, 'subject' => $subject, 'reason' => $reason, 'created_at' => now()]);
    }

    public function purchase(User $buyer): int
    {
        return $this->locked(function () use ($buyer) {
            $existing = DB::table('sim_purchases')->where('user_id', $buyer->id)->first();
            if ($existing) {
                return $existing->id;
            }
            $id = DB::table('sim_purchases')->insertGetId(['user_id' => $buyer->id, 'amount' => config('simulation.price'), 'status' => 'purchased', 'created_at' => now(), 'updated_at' => now()]);
            $sponsor = $buyer->profile?->sponsor_id;
            $seen = [$buyer->id];
            foreach (config('simulation.rewards') as $level => $amount) {
                if (! $sponsor || in_array($sponsor, $seen, true)) {
                    break;
                }
                $seen[] = $sponsor;
                if (DB::table('sim_purchases')->where('user_id', $sponsor)->where('status', 'purchased')->exists()) {
                    DB::table('sim_ledger')->insert(['user_id' => $sponsor, 'purchase_id' => $id, 'reference' => "purchase:$id:level:$level", 'type' => 'commission', 'amount' => $amount, 'level' => $level, 'created_at' => now()]);
                }
                // An ineligible sponsor does not compress or redistribute the levels.
                $sponsor = DB::table('profiles')->where('user_id', $sponsor)->value('sponsor_id');
            }
            $this->audit($buyer->id, 'demo_purchase', "purchase:$id", 'Explicit simulation; no payment collected');

            return $id;
        });
    }

    public function withdraw(User $user, int $amount, string $key): int
    {
        return $this->locked(function () use ($user, $amount, $key) {
            $existing = DB::table('sim_withdrawals')->where('request_key', $key)->first();
            if ($existing) {
                abort_unless($existing->user_id == $user->id && $existing->amount === $amount, 409);

                return $existing->id;
            }
            if ($amount < config('simulation.minimum_withdrawal') || $amount > $this->balance($user->id)) {
                throw ValidationException::withMessages(['amount' => 'Request at least ₹500 and no more than your available simulated balance.']);
            }
            $id = DB::table('sim_withdrawals')->insertGetId(['user_id' => $user->id, 'request_key' => $key, 'amount' => $amount, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
            DB::table('sim_ledger')->insert(['user_id' => $user->id, 'withdrawal_id' => $id, 'reference' => "withdrawal:$id:reserve", 'type' => 'withdrawal_reserve', 'amount' => -$amount, 'created_at' => now()]);
            $this->audit($user->id, 'demo_withdrawal_requested', "withdrawal:$id", 'Simulated balance reserved; no UPI details or real transfer');

            return $id;
        });
    }

    public function review(User $actor, int $id, string $action, string $reason): void
    {
        $this->locked(function () use ($actor, $id, $action, $reason) {
            abort_unless(in_array($this->role($actor->id), ['super', 'reviewer']), 403);
            $item = DB::table('sim_withdrawals')->find($id);
            abort_unless($item, 404);
            abort_if($item->user_id == $actor->id, 403, 'A reviewer cannot review their own request.');
            $allowed = ['approve' => ['pending', 'approved'], 'reject' => ['pending', 'rejected'], 'complete' => ['approved', 'simulated_paid'], 'fail' => ['approved', 'failed']];
            abort_unless(isset($allowed[$action]), 422);
            [$from, $to] = $allowed[$action];
            if ($item->status === $to) {
                return;
            }
            abort_unless($item->status === $from, 409, 'This request has already changed state.');
            if (in_array($action, ['approve', 'complete']) && $this->balance($item->user_id) < 0) {
                throw ValidationException::withMessages(['review' => 'A refund created a simulated balance deficit. Reject or fail this request before continuing.']);
            }
            DB::table('sim_withdrawals')->where('id', $id)->update(['status' => $to, 'updated_at' => now()]);
            if (in_array($action, ['reject', 'fail'])) {
                DB::table('sim_ledger')->insert(['user_id' => $item->user_id, 'withdrawal_id' => $id, 'reference' => "withdrawal:$id:release", 'type' => 'withdrawal_release', 'amount' => $item->amount, 'created_at' => now()]);
            }
            $this->audit($actor->id, "demo_withdrawal_$action", "withdrawal:$id", $reason);
        });
    }

    public function refund(User $actor, int $id, string $reason): void
    {
        $this->locked(function () use ($actor, $id, $reason) {
            abort_unless($this->role($actor->id) === 'super', 403);
            $purchase = DB::table('sim_purchases')->find($id);
            abort_unless($purchase, 404);
            if ($purchase->status === 'refunded') {
                return;
            }
            DB::table('sim_purchases')->where('id', $id)->update(['status' => 'refunded', 'updated_at' => now()]);
            foreach (DB::table('sim_ledger')->where('purchase_id', $id)->where('type', 'commission')->get() as $credit) {
                DB::table('sim_ledger')->insert(['user_id' => $credit->user_id, 'purchase_id' => $id, 'reference' => "refund:$id:credit:{$credit->id}", 'type' => 'commission_reversal', 'amount' => -$credit->amount, 'level' => $credit->level, 'created_at' => now()]);
            }
            $this->audit($actor->id, 'demo_purchase_refunded', "purchase:$id", $reason);
        });
    }
}
