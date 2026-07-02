<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name') . ' — Premium Bike Accessories')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased bg-white text-brand-black" x-data="{ mobileOpen: false }">
    {{-- Top bar (database-managed) --}}
    @if(isset($topBarAnnouncements) && $topBarAnnouncements->isNotEmpty())
    <div class="bg-brand-black text-white text-xs py-2 hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex {{ $topBarAnnouncements->count() > 1 ? 'justify-between' : 'justify-center' }} items-center gap-4">
            @foreach($topBarAnnouncements as $announcement)
                <x-announcement-item :announcement="$announcement" class="hover:text-brand-red transition-colors" />
            @endforeach
        </div>
    </div>
    @endif

    {{-- Navbar --}}
    <nav class="bg-white border-b border-zinc-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 lg:h-12 w-auto">
                </a>

                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }} transition-colors">Home</a>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold {{ request()->routeIs('products.*') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }} transition-colors">Shop All</a>
                    <a href="{{ route('products.index', ['category' => 'helmet']) }}" class="text-sm font-semibold text-brand-black hover:text-brand-red transition-colors">Helmets</a>
                    <a href="{{ route('products.index', ['category' => 'riding-jacket']) }}" class="text-sm font-semibold text-brand-black hover:text-brand-red transition-colors">Riding Gear</a>
                </div>

                <div class="flex items-center gap-3 lg:gap-5">
                    <a href="{{ route('cart.index') }}" class="relative p-2 text-brand-black hover:text-brand-red transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        @if($cartCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 bg-brand-red text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden sm:inline text-sm font-semibold text-brand-black hover:text-brand-red">Account</a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">@csrf<button type="submit" class="text-sm font-semibold text-zinc-500 hover:text-brand-red">Logout</button></form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline text-sm font-semibold text-brand-black hover:text-brand-red">Login</a>
                        <a href="{{ route('register') }}" class="hidden sm:inline text-sm font-semibold bg-brand-red text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">Register</a>
                    @endauth
                    <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2 text-brand-black">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <div x-show="mobileOpen" x-cloak class="lg:hidden border-t border-zinc-100 bg-white px-4 py-4 space-y-3">
            <a href="{{ route('home') }}" class="block text-sm font-semibold py-2">Home</a>
            <a href="{{ route('products.index') }}" class="block text-sm font-semibold py-2">Shop All</a>
            <a href="{{ route('products.index', ['category' => 'helmet']) }}" class="block text-sm font-semibold py-2">Helmets</a>
            @guest
            <a href="{{ route('login') }}" class="block text-sm font-semibold py-2 text-brand-red">Login / Register</a>
            @endguest
        </div>
    </nav>

    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    </div>
    @endif
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 border border-red-200 text-brand-red px-4 py-3 rounded-xl text-sm">{{ session('error') }}</div>
    </div>
    @endif

    <main>@yield('content')</main>

    <footer class="bg-brand-black text-zinc-400 mt-0 relative overflow-hidden">
        {{-- Animated top wave strip --}}
        <div class="h-1 bg-gradient-to-r from-transparent via-brand-red to-transparent"></div>
        <div class="absolute inset-0 opacity-5 pointer-events-none">
            <div class="absolute -top-20 -left-20 w-64 h-64 bg-brand-red rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-20 -right-20 w-64 h-64 bg-brand-red rounded-full blur-3xl animate-pulse" style="animation-delay: 1.5s"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 relative">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="md:col-span-1">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-14 w-auto mb-4 brightness-0 invert">
                    <p class="text-sm leading-relaxed">India's trusted destination for premium bike accessories, helmets, riding gear & more.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Shop</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('products.index') }}" class="hover:text-brand-red transition-colors">All Products</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'helmet']) }}" class="hover:text-brand-red transition-colors">Helmets</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'gloves']) }}" class="hover:text-brand-red transition-colors">Gloves</a></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-brand-red transition-colors">Cart</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Account</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-brand-red transition-colors">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-brand-red transition-colors">Register</a></li>
                        <li><a href="{{ route('orders.index') }}" class="hover:text-brand-red transition-colors">My Orders</a></li>
                        <li><a href="{{ route('dashboard') }}" class="hover:text-brand-red transition-colors">Dashboard</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4 text-sm uppercase tracking-wider">Contact</h4>
                    <p class="text-sm">support@bikeworld.com</p>
                    <p class="text-sm mt-1">+91 98765 43210</p>
                    <p class="text-sm mt-1">Mumbai, Maharashtra, India</p>
                </div>
            </div>
            <div class="border-t border-zinc-800 mt-10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm">
                <span>&copy; {{ date('Y') }} <span class="text-brand-red font-semibold">BIKE</span><span class="text-white font-semibold">WORLD</span>. All rights reserved.</span>
                <div class="flex gap-4">
                    <span>🔒 Secure Checkout</span>
                    <span>✓ Genuine Products</span>
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
