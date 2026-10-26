@extends('layouts.app')
@section('title', 'Product and wallet simulation')
@section('content')
<header class="topbar"><x-brand /><nav aria-label="Simulation navigation"><a href="{{ route('dashboard') }}">Dashboard</a>@if($role)<a href="{{ route('simulation.admin') }}">Demo admin</a>@endif</nav></header>
<main id="main" class="container section simulation">
<p class="eyebrow">LOCAL DEMO · NO REAL MONEY</p><h1>Try your product & wallet.</h1>
<div class="notice"><strong>Simulation only.</strong> These actions do not collect payments, deliver a product, or send money. Demo eligibility and balances have no cash value.</div>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($errors->any())<div class="notice" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="dashboard-columns">
<section class="panel"><p class="eyebrow">THE PRODUCT</p><h2>Chain Earn PDF guide</h2><p>Planned price: ₹1,000. The PDF will be provided later; downloads are unavailable.</p>
@if(!$purchase)
<form method="POST" action="{{ route('simulation.purchase') }}">@csrf<label><input type="checkbox" name="simulation_acknowledged" value="1" required> I understand this is a demo purchase with no payment or PDF delivery.</label><button class="button" type="submit">Simulate ₹1,000 purchase</button></form>
@else
<p class="badge">{{ $purchase->status === 'purchased' ? 'Demo entitlement active · PDF pending' : 'Demo entitlement revoked · Refunded' }}</p><p>Purchase #{{ $purchase->id }}. Only one demo purchase per account is supported.</p>
@endif
<p class="fine-print">Demo rewards: level 1 ₹500, level 2 ₹250, level 3 ₹100, level 4 ₹50. A sponsor must have an active demo purchase when the invited member purchases. No retroactive rewards or redistribution.</p></section>
<section class="panel"><p class="eyebrow">SIMULATED WALLET</p><h2>₹{{ number_format($balance / 100, 2) }}</h2><p>Available after reservations and reversals. This is not real money.</p>
@if($balance < 0)<p role="alert">Refund reversals created a demo deficit. Future demo rewards offset it; withdrawals are blocked until sufficient balance is available.</p>@endif
<form method="POST" action="{{ route('simulation.withdraw') }}">@csrf<input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}"><label for="amount">Demo withdrawal amount (whole rupees)</label><input id="amount" type="number" name="amount" min="500" max="10000000" step="1" required value="{{ old('amount', 500) }}"><label><input type="checkbox" name="simulation_acknowledged" value="1" required> I understand no real payout will be made.</label><button class="button" type="submit">Request demo withdrawal</button></form>
<p class="fine-print">Minimum ₹500. Demo fee: ₹0. No UPI details are collected. Real payout fees and rules remain to be configured.</p></section>
</div>
<section class="panel"><h2>Demo withdrawal requests</h2><div class="table-scroll"><table><thead><tr><th>Request</th><th>Amount</th><th>Status</th></tr></thead><tbody>@forelse($withdrawals as $item)<tr><td>#{{ $item->id }}</td><td>₹{{ number_format($item->amount / 100, 2) }}</td><td>{{ str_replace('_', ' ', $item->status) }}</td></tr>@empty<tr><td colspan="3">No demo withdrawal requests yet.</td></tr>@endforelse</tbody></table></div>{{ $withdrawals->withQueryString()->links() }}</section>
<section class="panel"><h2>Demo transaction ledger</h2><p>Reservations reduce available balance. Rejection or failure releases them. Refunds reverse the original rewards.</p><div class="table-scroll"><table><thead><tr><th>Entry</th><th>Type</th><th>Level</th><th>Change</th></tr></thead><tbody>@forelse($ledger as $entry)<tr><td>#{{ $entry->id }}</td><td>{{ str_replace('_', ' ', $entry->type) }}</td><td>{{ $entry->level ?? '—' }}</td><td>₹{{ number_format($entry->amount / 100, 2) }}</td></tr>@empty<tr><td colspan="4">No demo transactions yet. Purchases by eligible referrals create simulated rewards.</td></tr>@endforelse</tbody></table></div>{{ $ledger->withQueryString()->links() }}</section>
</main>
@endsection
