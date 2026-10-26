@extends('layouts.app')
@section('title', config('policies.'.$policyKey.'.title'))
@section('content')
@php($policy = config('policies.'.$policyKey))
<header class="topbar"><x-brand /><nav aria-label="Main navigation"><a class="nav-link" href="{{ route('home') }}#products">EverPass</a><a class="nav-link" href="{{ route('contact') }}">Contact</a></nav></header>
<main id="main" class="container policy-page">
    <p class="eyebrow">CHAIN EARN / INFORMATION</p>
    <h1>{{ $policy['title'] }}</h1>
    <p class="policy-intro">{{ $policy['intro'] }}</p>
    <p class="fine-print">Last updated: 26 October 2026</p>
    @if(collect(config('business'))->contains(fn ($value) => blank($value)))
    <div class="notice" role="note">Business contact details are awaiting confirmation. This preview is not yet ready for purchases.</div>
    @endif
    <nav class="policy-links" aria-label="Information pages">
        @foreach(config('policies') as $key => $item)
        <a class="text-link" href="{{ route($key) }}" @if($key === $policyKey) aria-current="page" @endif>{{ $item['title'] }}</a>
        @endforeach
    </nav>
    <article class="panel policy-content">
        @foreach($policy['sections'] as $heading => $paragraph)
        <section><h2>{{ $heading }}</h2><p>{{ $paragraph }}</p></section>
        @endforeach
        @if($policyKey === 'privacy-policy')
        <p><a class="text-link" href="https://razorpay.com/privacy-policy/" rel="external">Read Razorpay’s Privacy Policy</a></p>
        @endif
        <section aria-labelledby="business-details"><h2 id="business-details">Business & support details</h2>
            <dl class="business-details">
                @foreach(['legal_name' => 'Merchant legal name', 'address' => 'Business address', 'email' => 'Support email', 'phone' => 'Support phone', 'privacy_contact' => 'Privacy / grievance contact', 'privacy_email' => 'Privacy / grievance email'] as $field => $label)
                <dt>{{ $label }}</dt><dd>{{ config('business.'.$field) ?: 'Awaiting confirmation' }}</dd>
                @endforeach
            </dl>
        </section>
    </article>
</main>
@endsection
