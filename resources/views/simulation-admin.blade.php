@extends('layouts.app')
@section('title', 'Simulation administration')
@section('content')
<header class="topbar"><x-brand /><nav aria-label="Demo admin navigation"><a href="{{ route('simulation') }}">Demo wallet</a><a href="{{ route('dashboard') }}">Dashboard</a></nav></header>
<main id="main" class="container section simulation">
<p class="eyebrow">LOCAL DEMO ADMIN · {{ $role === 'super' ? 'SUPER ADMIN' : 'WITHDRAWAL REVIEWER' }}</p><h1>Review the simulation.</h1>
<div class="notice">All actions are simulated and audited. No money is sent. You cannot review your own withdrawal requests. Reviewers can manage withdrawals; only super admins can reverse purchases and read the audit log.</div>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="panel"><h2>Demo withdrawals</h2><div class="table-scroll"><table><thead><tr><th>Request</th><th>Member</th><th>Amount / status</th><th>Review</th></tr></thead><tbody>
@forelse($withdrawals as $item)
<tr><td>#{{ $item->id }}</td><td>{{ $item->name }}</td><td>₹{{ number_format($item->amount / 100, 2) }}<br>{{ str_replace('_', ' ', $item->status) }}</td><td>
@if($item->user_id == auth()->id())<p>Another reviewer must review this request.</p>
@elseif(in_array($item->status, ['pending', 'approved']))
<form method="POST" action="{{ route('simulation.review', $item->id) }}">@csrf<label for="action-{{ $item->id }}">Demo action</label><select id="action-{{ $item->id }}" name="action" required>@if($item->status === 'pending')<option value="approve">Approve demo request</option><option value="reject">Reject and release balance</option>@else<option value="complete">Mark simulated payout complete</option><option value="fail">Mark failed and release balance</option>@endif</select><label for="reason-{{ $item->id }}">Reason</label><input id="reason-{{ $item->id }}" name="reason" minlength="5" maxlength="500" required><button class="button small" type="submit">Apply demo action</button></form>
@else<p>Final state</p>@endif
</td></tr>
@empty<tr><td colspan="4">No demo requests to review.</td></tr>@endforelse
</tbody></table></div>{{ $withdrawals->withQueryString()->links() }}</section>
@if($role === 'super')
<section class="panel"><h2>Demo purchases and reversals</h2><p>Reversals revoke the demo entitlement and debit its original commission recipients. Existing balances may become negative; future rewards offset the deficit.</p><div class="table-scroll"><table><thead><tr><th>Purchase</th><th>Member / status</th><th>Reverse</th></tr></thead><tbody>@forelse($purchases as $item)<tr><td>#{{ $item->id }}</td><td>{{ $item->name }}<br>{{ $item->status }}</td><td>@if($item->status === 'purchased')<form method="POST" action="{{ route('simulation.refund', $item->id) }}">@csrf<label for="refund-{{ $item->id }}">Reason for demo reversal</label><input id="refund-{{ $item->id }}" name="reason" minlength="5" maxlength="500" required><button class="button small" type="submit">Reverse demo purchase</button></form>@else Already reversed @endif</td></tr>@empty<tr><td colspan="3">No demo purchases yet.</td></tr>@endforelse</tbody></table></div>{{ $purchases->withQueryString()->links() }}</section>
<section class="panel"><h2>Demo audit log</h2><div class="table-scroll"><table><thead><tr><th>Event / actor</th><th>Action / subject</th><th>Reason</th></tr></thead><tbody>@forelse($audit as $entry)<tr><td>#{{ $entry->id }} / user #{{ $entry->actor_id }}<br>{{ $entry->created_at }}</td><td>{{ $entry->action }}<br>{{ $entry->subject }}</td><td>{{ $entry->reason }}</td></tr>@empty<tr><td colspan="3">No demo audit events yet.</td></tr>@endforelse</tbody></table></div>{{ $audit->withQueryString()->links() }}</section>
@endif
</main>
@endsection
