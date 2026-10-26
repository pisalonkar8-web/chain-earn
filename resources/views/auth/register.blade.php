@extends('layouts.app')
@section('title', 'Create an account')
@section('content')
<header class="topbar"><x-brand /><a class="text-link" href="{{ route('login') }}">Log in ↗</a></header>
<main id="main" class="auth-layout"><section class="auth-intro"><p class="eyebrow">YOUR NEXT CHAPTER</p><h1>Good things<br>start with<br><em>connection.</em></h1><p>Create your account to explore Chain Earn and your personal dashboard.</p><span class="badge">Phase 1 · Community preview</span></section><section class="auth-card"><h2>Create your account</h2><p>Welcome to a world of shared possibilities.</p>
@if(request()->filled('ref'))<div class="notice">You opened a referral invitation. Check the optional referral code below. Your sponsor is saved when you register; rewards remain demo only.</div>@endif
@include('auth.errors')
<form method="POST" action="{{ route('register') }}">@csrf
<label for="name">Full name</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" autofocus>
<label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email"><small>Use a lowercase email address.</small>
<label for="phone">Indian mobile number</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="30" autocomplete="tel" placeholder="+91 98765 43210"><small>10 digits starting with 6–9; +91 is optional.</small><label for="referral_code">Referral code (optional)</label><input id="referral_code" name="referral_code" value="{{ old('referral_code', request('ref')) }}" maxlength="36"><small>Leave blank if nobody invited you. Your sponsor cannot be changed after registration.</small><label for="password">Password</label><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"><small>At least 8 characters, including a letter and a number.</small>
<label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
<button class="button full" type="submit">Create account ↗</button></form><p class="auth-switch">Already a member? <a href="{{ route('login') }}">Log in</a></p></section></main>
@endsection
