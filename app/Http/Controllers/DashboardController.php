<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\Simulation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;
        // Count only this member's descendants, bounded to the four planned levels.
        // Each level is aggregated in SQL; do not load the entire network into PHP.
        $descendants = Profile::query()->select('user_id')->where('sponsor_id', $user->id);
        $levelCounts = [];
        for ($level = 1; $level <= 4; $level++) {
            $levelCounts[$level] = (clone $descendants)->count();
            $descendants = Profile::query()->select('user_id')->whereIn('sponsor_id', $descendants);
        }

        $directMembers = Profile::query()->where('sponsor_id', $user->id)
            ->with('user:id,name,email_verified_at')->latest('id')->paginate(15)->fragment('network');

        $simulation = app()->environment(['local', 'testing']) && config('simulation.enabled');
        $balance = $simulation ? app(Simulation::class)->balance($user->id) : 0;

        return view('dashboard', [
            'simulationEnabled' => (bool) $simulation,
            'demoStats' => ['Simulated available balance' => '₹'.number_format($balance / 100, 2)],
            'referralLink' => $profile ? route('register', ['ref' => $profile->referral_code]) : null,
            'levelCounts' => $levelCounts,
            'directMembers' => $directMembers,
        ]);
    }
}
