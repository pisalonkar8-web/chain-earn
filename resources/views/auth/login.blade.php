@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<header class="topbar"><x-brand /><a class="text-link" href="{{ route('register') }}">Create account ↗</a></header>
<main id="main" class="auth-layout"><section class="auth-intro"><p class="eyebrow">WELCOME BACK</p><h1>Your circle.<br>Your progress.<br><em>Your space.</em></h1><p>Pick up where you left off in your personal Chain Earn dashboard.</p></section><section class="auth-card"><h2>Good to see you again.</h2><p>Log in to your Chain Earn account.</p>@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif @include('auth.errors')<form method="POST" action="{{ route('login') }}">@csrf
<label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
<label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
<p><a class="text-link" href="{{ route('password.request') }}">Forgot your password?</a></p><label class="checkbox-label"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
<button class="button full" type="submit">Log in ↗</button></form><p class="auth-switch">New here? <a href="{{ route('register') }}">Create an account</a></p></section></main>
@endsection
