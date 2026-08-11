<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoMeta = app(\App\Services\SeoService::class)->resolve(
            $seo ?? null,
            trim(strip_tags($__env->yieldContent('title'))) ?: null
        );
    @endphp
    <x-seo-meta :meta="$seoMeta" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        :root {
            --brand-red: #E31E24;
            --brand-black: #0A0A0A;
            --brand-dark: #141414;
            --theme-ticker-bg: #E31E24;
        }
        @if(!empty($homeTheme))
        :root {
            {!! $homeTheme->cssVariablesString() !!};
        }
        @endif
        .text-brand-red { color: var(--brand-red) !important; }
        .bg-brand-red { background-color: var(--brand-red) !important; }
        .border-brand-red { border-color: var(--brand-red) !important; }
        .text-brand-black { color: var(--brand-black) !important; }
        .bg-brand-black { background-color: var(--brand-black) !important; }
        .bg-brand-dark { background-color: var(--brand-dark) !important; }
        .hover\:text-brand-red:hover { color: var(--brand-red) !important; }
        .hover\:bg-brand-red:hover { background-color: var(--brand-red) !important; }
        .hover\:border-brand-red:hover { border-color: var(--brand-red) !important; }
        .focus\:border-brand-red:focus { border-color: var(--brand-red) !important; }
        .shadow-brand-red\/40 { --tw-shadow-color: color-mix(in srgb, var(--brand-red) 40%, transparent); }
        [x-cloak]{display:none!important}
    </style>
</head>
<body class="font-sans antialiased bg-white text-brand-black overflow-x-hidden w-full{{ !empty($homeTheme) ? ' home-theme-active' : '' }}" @if(!empty($homeTheme)) data-home-theme="{{ $homeTheme->preset }}" @endif x-data="{ mobileOpen: false, wishlistCount: {{ $wishlistCount ?? 0 }} }" @wishlist-updated.window="wishlistCount = $event.detail.count">
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
    <nav class="bg-white border-b border-zinc-100 sticky top-0 z-50 shadow-sm w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full min-w-0">
            <div class="flex justify-between items-center h-16 lg:h-20 min-w-0 gap-2">
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink min-w-0 max-w-[45%] sm:max-w-none">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 lg:h-12 w-auto max-w-full object-contain">
                </a>

                <form action="{{ route('search.index') }}" method="GET" class="hidden lg:flex flex-1 max-w-lg mx-4 min-w-0">
                    <div class="relative w-full">
                        <input type="search" name="q" value="{{ request('q') }}"
                            placeholder="Search helmets, gloves, accessories…"
                            class="w-full rounded-xl border border-zinc-200 bg-zinc-50 pl-10 pr-4 py-2.5 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
                        <svg class="w-5 h-5 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </form>

                <div class="hidden lg:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }} transition-colors">Home</a>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold {{ request()->routeIs('products.*') && ! request('category') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }} transition-colors">Shop All</a>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold {{ request()->routeIs('blog.*') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }} transition-colors">Blog</a>

                    @if(($menuCategories ?? collect())->isNotEmpty())
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open"
                            class="flex items-center gap-1 text-sm font-semibold transition-colors {{ request()->routeIs('products.*') && request('category') ? 'text-brand-red' : 'text-brand-black hover:text-brand-red' }}"
                            :aria-expanded="open">
                            Categories
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="absolute left-0 top-full pt-2 z-[100]">
                            <div class="w-56 min-w-max max-w-xs bg-white border border-zinc-100 rounded-xl shadow-xl py-2 max-h-80 overflow-y-auto">
                            @foreach($menuCategories as $category)
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                               @click="open = false"
                               class="block px-4 py-2.5 text-sm font-medium whitespace-nowrap {{ request('category') === $category->slug ? 'text-brand-red bg-red-50' : 'text-brand-black hover:text-brand-red hover:bg-zinc-50' }} transition-colors">
                                {{ $category->name }}
                            </a>
                            @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="flex items-center gap-3 lg:gap-5">
                    @auth
                    <a href="{{ route('account.wishlist') }}" class="relative p-2 text-brand-black hover:text-brand-red transition-colors" title="Wishlist">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-show="wishlistCount > 0" x-cloak class="absolute -top-0.5 -right-0.5 bg-brand-red text-white text-[10px] font-bold rounded-full min-w-[1.25rem] h-5 px-1 flex items-center justify-center" x-text="wishlistCount"></span>
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="relative p-2 text-brand-black hover:text-brand-red transition-colors" title="Wishlist">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </a>
                    @endauth
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
            <form action="{{ route('search.index') }}" method="GET" class="pb-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search products…"
                    class="w-full rounded-xl border border-zinc-200 px-4 py-2.5 text-sm focus:border-brand-red focus:ring-2 focus:ring-brand-red/15 outline-none">
            </form>
            <a href="{{ route('home') }}" class="block text-sm font-semibold py-2">Home</a>
            <a href="{{ route('products.index') }}" class="block text-sm font-semibold py-2">Shop All</a>
            <a href="{{ route('blog.index') }}" class="block text-sm font-semibold py-2">Blog</a>

            @if(($menuCategories ?? collect())->isNotEmpty())
            <div x-data="{ catOpen: false }">
                <button type="button" @click="catOpen = !catOpen" class="flex items-center justify-between w-full text-sm font-semibold py-2">
                    <span>Categories</span>
                    <svg class="w-4 h-4 transition-transform" :class="catOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="catOpen" x-cloak class="pl-3 border-l-2 border-zinc-100 ml-1 space-y-1 mt-1">
                    @foreach($menuCategories as $category)
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}"
                       class="block text-sm py-2 {{ request('category') === $category->slug ? 'text-brand-red font-semibold' : 'text-zinc-600' }}">
                        {{ $category->name }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @auth
            <a href="{{ route('account.wishlist') }}" class="block text-sm font-semibold py-2">Wishlist</a>
            @endauth
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

    <main class="overflow-x-hidden w-full max-w-full">@yield('content')</main>

    <x-shop-footer />

    @if(request()->routeIs('home') && !empty($homeTheme))
        <x-home-theme-decoration :home-theme="$homeTheme" />
    @endif

    @stack('scripts')
</body>
</html>
