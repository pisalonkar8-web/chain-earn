@extends('auth.security')
@section('title', 'Verify your email')
@section('heading', 'Check your email')
@section('security-content')
<p>We sent a verification link to {{ auth()->user()->email }}. Open it to access your demo dashboard. Links expire after 60 minutes.</p>
<form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button full" type="submit">Resend verification email</button></form>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" type="submit">Log out</button></form>
@endsection
