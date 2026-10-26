@extends('layouts.app')
@section('title', 'Your dashboard')
@section('content')
<div class="dashboard-shell">
<aside class="sidebar"><x-brand /><button class="menu-toggle" type="button" aria-expanded="false" aria-controls="sidebar-menu">Menu ☰</button><div id="sidebar-menu" class="sidebar-menu"><p class="eyebrow">YOUR WORKSPACE</p><nav aria-label="Dashboard navigation"><a class="active" href="{{ route('dashboard') }}" aria-current="page">▦ Overview</a><a href="#network">◎ My network</a><a href="#referral">↗ Referral link</a><a href="{{ route('home') }}#products">◇ Products</a></nav><div class="sidebar-note"><span class="badge">Account preview</span><p>Your registered community, in one place.</p></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Log out ↗</button></form></div></aside>
<main id="main" class="dashboard-main">
<header class="dashboard-top"><span>Workspace <span class="muted">/ Overview</span></span><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span></header>
<div class="dashboard-heading"><div><p class="eyebrow">YOUR PERSONAL OVERVIEW</p><h1>Hello, {{ auth()->user()->name }} <span class="wave">✦</span></h1><p>Your account and registered referral network.</p></div><span class="badge">Account preview</span></div>
<div class="notice"><strong>Demo data · Financial preview only.</strong> Wallet amounts come from demo transactions and have no cash value. No money is collected, transferred, or available for withdrawal. Network counts below come from actual registrations, not purchases or earning eligibility.</div>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@if($simulationEnabled)
<section class="panel" aria-labelledby="simulation-heading"><p class="eyebrow">LOCAL DEMO</p><h2 id="simulation-heading">Explore the product & wallet simulation</h2><p>Try a fictional purchase and see demo rewards and withdrawal requests. No real money moves. The guide PDF is not available yet.</p><a class="button small" href="{{ route('simulation') }}">Open demo simulation ↗</a></section>
@endif
<section class="stats-grid" aria-label="Account statistics">
<article class="stat-card"><div><span>Direct referrals</span><small>REGISTERED</small></div><strong>{{ number_format($levelCounts[1]) }}</strong><p>Accounts invited directly by you</p></article>
<article class="stat-card"><div><span>Network members</span><small>REGISTERED</small></div><strong>{{ number_format(array_sum($levelCounts)) }}</strong><p>Registered descendants across four levels</p></article>
@foreach($demoStats as $label => $value)<article class="stat-card"><div><span>{{ $label }}</span><small>DEMO</small></div><strong>{{ $value }}</strong><p>Illustrative preview value</p></article>@endforeach
</section>
<div class="dashboard-columns"><section id="referral" class="panel referral-panel"><p class="eyebrow">GROW YOUR CIRCLE</p><h2>A connection starts with you.</h2><p>Invited registrations save you as their sponsor. Registration alone does not generate a commission.</p>@if($referralLink)<label for="referral-link">Your referral link</label><div class="copy-row"><input id="referral-link" readonly value="{{ $referralLink }}"><button type="button" class="button small" id="copy-referral">Copy link</button></div><p id="copy-status" class="fine-print" role="status" aria-live="polite"></p>@else<p>Your profile has not been created yet.</p>@endif</section><section class="panel"><p class="eyebrow">UP NEXT</p><h2>Made for your next step.</h2><p>Preview our planned digital guide. No product sales or payments are available.</p><a class="text-link" href="{{ route('home') }}#products">Discover the guide ↗</a><span class="collection-symbol" aria-hidden="true">✦</span></section></div>
<section id="network" class="panel network-panel">
<div class="section-heading"><div><p class="eyebrow">YOUR COMMUNITY</p><h2>Your registered network</h2></div><span class="badge">Actual registrations</span></div>
<p>Level 1: {{ number_format($levelCounts[1]) }} · Level 2: {{ number_format($levelCounts[2]) }} · Level 3: {{ number_format($levelCounts[3]) }} · Level 4: {{ number_format($levelCounts[4]) }}</p>
<div class="table-scroll"><table><caption class="sr-only">Your direct referrals, newest first. Contact details are private.</caption><thead><tr><th>Direct member</th><th>Joined</th><th>Email verification</th></tr></thead><tbody>
@forelse($directMembers as $member)
<tr><td><span class="table-avatar">{{ mb_substr($member->user->name, 0, 1) }}</span>{{ $member->user->name }}</td><td>{{ $member->created_at->format('d M Y') }}</td><td><span class="badge">{{ $member->user->hasVerifiedEmail() ? 'Verified' : 'Pending' }}</span></td></tr>
@empty
<tr><td colspan="3">No direct referrals yet. Share your referral link to invite your first member.</td></tr>
@endforelse
</tbody></table></div>
{{ $directMembers->links() }}
</section></main></div>
@endsection

