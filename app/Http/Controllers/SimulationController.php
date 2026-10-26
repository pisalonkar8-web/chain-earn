<?php

namespace App\Http\Controllers;

use App\Services\Simulation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulationController extends Controller
{
    public function index(Request $request, Simulation $simulation)
    {
        Simulation::guard();
        $user = $request->user()->id;

        return view('simulation', [
            'balance' => $simulation->balance($user),
            'purchase' => DB::table('sim_purchases')->where('user_id', $user)->first(),
            'ledger' => DB::table('sim_ledger')->where('user_id', $user)->latest('id')->paginate(15, ['*'], 'ledger_page'),
            'withdrawals' => DB::table('sim_withdrawals')->where('user_id', $user)->latest('id')->paginate(10, ['*'], 'requests_page'),
            'requestKey' => (string) Str::uuid(),
            'role' => $simulation->role($user),
        ]);
    }

    public function purchase(Request $request, Simulation $simulation)
    {
        Simulation::guard();
        $request->validate(['simulation_acknowledged' => ['accepted']]);
        $simulation->purchase($request->user());

        return to_route('simulation')->with('status', 'Demo purchase recorded. No payment was collected. The PDF is not available yet.');
    }

    public function withdraw(Request $request, Simulation $simulation)
    {
        Simulation::guard();
        $data = $request->validate(['amount' => ['required', 'integer', 'min:500', 'max:10000000'], 'request_key' => ['required', 'uuid'], 'simulation_acknowledged' => ['accepted']]);
        $simulation->withdraw($request->user(), (int) $data['amount'] * 100, $data['request_key']);

        return to_route('simulation')->with('status', 'Demo withdrawal requested. No money will be sent.');
    }

    public function admin(Request $request, Simulation $simulation)
    {
        Simulation::guard();
        $role = $simulation->role($request->user()->id);
        abort_unless(in_array($role, ['super', 'reviewer']), 403);

        return view('simulation-admin', [
            'role' => $role,
            'withdrawals' => DB::table('sim_withdrawals')->join('users', 'users.id', '=', 'sim_withdrawals.user_id')->select('sim_withdrawals.*', 'users.name')->latest('sim_withdrawals.id')->paginate(15, ['*'], 'requests_page'),
            'purchases' => $role === 'super' ? DB::table('sim_purchases')->join('users', 'users.id', '=', 'sim_purchases.user_id')->select('sim_purchases.*', 'users.name')->latest('sim_purchases.id')->paginate(15, ['*'], 'purchases_page') : null,
            'audit' => $role === 'super' ? DB::table('sim_audit')->latest('id')->paginate(20, ['*'], 'audit_page') : null,
        ]);
    }

    public function review(Request $request, int $id, Simulation $simulation)
    {
        Simulation::guard();
        $data = $request->validate(['action' => ['required', 'in:approve,reject,complete,fail'], 'reason' => ['required', 'string', 'min:5', 'max:500']]);
        $simulation->review($request->user(), $id, $data['action'], $data['reason']);

        return to_route('simulation.admin')->with('status', 'Demo withdrawal updated. No real payout was initiated.');
    }

    public function refund(Request $request, int $id, Simulation $simulation)
    {
        Simulation::guard();
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $simulation->refund($request->user(), $id, $data['reason']);

        return to_route('simulation.admin')->with('status', 'Demo purchase reversed and associated simulated commissions reversed.');
    }
}
