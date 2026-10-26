<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Chain Earn: discover digital products and explore referral rewards.">
    <title>@yield('title', 'Welcome') · Chain Earn</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    @auth
        @if(app()->environment(['local', 'testing']) && config('simulation.enabled'))
            <div class="container"><a class="text-link" href="{{ route('simulation') }}">Open product & wallet simulation →</a></div>
        @endif
    @endauth
    @yield('content')
    <footer aria-label="Site footer">
    <nav class="policy-links footer-policies" aria-label="Policies and business information">
        @foreach(config('policies') as $key => $policy)
        <a href="{{ route($key) }}">{{ $policy['title'] }}</a>
        @endforeach
    </nav>
    <div class="site-footer">© {{ date('Y') }} Chain Earn <span>Digital products. Shared possibilities.</span></div>
    </footer>
</body>
</html>
