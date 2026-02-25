<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cashback System')</title>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/premium.css') }}">
    
    @yield('styles')
</head>
<body class="dark">
    <nav class="nav-container">
        <div class="logo gradient-text">CashbackFlow</div>
        <div class="nav-links" style="display: flex; gap: 24px;">
            <a href="/transactions" style="text-decoration: none; color: inherit; font-size: 0.9rem; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">Transactions</a>
            <a href="/categories" style="text-decoration: none; color: inherit; font-size: 0.9rem; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">Categories</a>
            <a href="/summary" style="text-decoration: none; color: inherit; font-size: 0.9rem; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">Summary</a>
        </div>

    </nav>

    <main class="container">
        @yield('content')
    </main>

    <script>
        // Check for dark mode preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            // document.body.classList.remove('dark');
            // document.documentElement.classList.remove('dark');
        }
    </script>
    @yield('scripts')
</body>
</html>
