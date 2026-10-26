@extends('layouts.app')
@section('content')
<header class="topbar"><x-brand /><a class="text-link" href="{{ route('home') }}">Home</a></header>
<main id="main" class="auth-layout"><section class="auth-intro"><p class="eyebrow">YOUR ACCOUNT</p><h1>A secure<br><em>start.</em></h1><p>Keep your Chain Earn account protected.</p></section><section class="auth-card">
<h2>@yield('heading')</h2>
@if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
@include('auth.errors')
@yield('security-content')
</section></main>
@endsection
