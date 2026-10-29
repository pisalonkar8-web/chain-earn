@extends('layouts.app')
@section('title', $heading)
@section('content')
<header class="topbar"><x-brand /><a class="text-link" href="{{ route('home') }}">Home</a></header>
<main id="main" class="container section">
    <p class="eyebrow">{{ $code }}</p>
    <h1>{{ $heading }}</h1>
    <p class="notice" role="status">{{ $message }}</p>
    <a class="button small" href="{{ route('home') }}">Return home</a>
</main>
@endsection
