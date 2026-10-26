@extends('auth.security')
@section('title', 'Reset your password')
@section('heading', 'Forgot your password?')
@section('security-content')
<p>Enter your email to request a password reset link.</p>
<form method="POST" action="{{ route('password.email') }}">@csrf
<label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
<button class="button full" type="submit">Send reset link</button></form>
<p class="auth-switch"><a href="{{ route('login') }}">Back to login</a></p>
@endsection
