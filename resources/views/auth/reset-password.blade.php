@extends('auth.security')
@section('title', 'Choose a new password')
@section('heading', 'Choose a new password')
@section('security-content')
<form method="POST" action="{{ route('password.update') }}">@csrf
<input type="hidden" name="token" value="{{ $token }}">
<label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" required autocomplete="email" autofocus>
<label for="password">New password</label><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"><small>At least 8 characters, including a letter and a number.</small>
<label for="password_confirmation">Confirm new password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
<button class="button full" type="submit">Reset password</button></form>
@endsection
