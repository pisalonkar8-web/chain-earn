<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulationAccess extends Command
{
    protected $signature = 'chain:simulation-access {email} {role : super, reviewer, or revoke}';

    protected $description = 'Grant or revoke local simulation access for an existing verified account';

    public function handle(): int
    {
        if (! app()->environment('local') || ! config('simulation.enabled')) {
            $this->error('This command is available only in the local simulation.');

            return self::FAILURE;
        }
        $role = $this->argument('role');
        $user = User::where('email', $this->argument('email'))->first();
        if (! in_array($role, ['super', 'reviewer', 'revoke']) || ! $user?->hasVerifiedEmail()) {
            $this->error('Select a valid role and an existing verified account.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($user, $role) {
            DB::table('sim_locks')->where('id', 1)->lockForUpdate()->first();
            if ($role === 'revoke') {
                DB::table('sim_access')->where('user_id', $user->id)->delete();
            } else {
                DB::table('sim_access')->updateOrInsert(['user_id' => $user->id], ['role' => $role]);
            }
            DB::table('sim_audit')->insert(['actor_id' => $user->id, 'action' => 'local_console_access_change', 'subject' => "user:{$user->id}", 'reason' => "Local console operator set simulation access to $role; actor_id identifies affected account", 'created_at' => now()]);
        });
        $this->info('Local simulation access updated. No production permissions were changed.');

        return self::SUCCESS;
    }
}
