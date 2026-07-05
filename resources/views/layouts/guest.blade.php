<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none!important}
        .auth-bg-image {
            background-image: url('https://images.unsplash.com/photo-1485965120188-e8f9d99c4595?w=1600&q=80');
            background-size: cover;
            background-position: center;
        }
        .auth-grid {
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 48px 48px;
        }
    </style>
</head>
<body class="font-sans antialiased text-brand-black bg-white">
    <div class="min-h-screen flex">
        {{-- Brand panel --}}
        <div class="hidden lg:flex lg:w-[52%] xl:w-[55%] relative overflow-hidden auth-bg-image">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-black via-brand-black/90 to-brand-red/40"></div>
            <div class="absolute inset-0 auth-grid opacity-60"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-brand-red/20 rounded-full blur-3xl"></div>
            <div class="absolute top-20 right-10 w-72 h-72 bg-brand-red/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 flex flex-col justify-between p-10 xl:p-14 w-full">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-11 w-auto brightness-0 invert">
                </a>

                <div class="max-w-lg">
                    <span class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/15 text-white text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
                        <span class="w-2 h-2 bg-brand-red rounded-full animate-pulse"></span>
                        Premium Riding Gear
                    </span>
                    <h1 class="text-4xl xl:text-5xl font-black text-white leading-[1.08] tracking-tight">
                        Ride further.<br>
                        <span class="text-brand-red">Gear up smarter.</span>
                    </h1>
                    <p class="mt-5 text-zinc-300 text-lg leading-relaxed">
                        Join thousands of riders shopping helmets, accessories, and parts — with fast delivery across India.
                    </p>

                    <ul class="mt-10 space-y-4">
                        @foreach(['Exclusive member deals & early access', 'Track orders & saved addresses', 'Secure checkout with COD & online pay'] as $feature)
                        <li class="flex items-center gap-3 text-zinc-200 text-sm">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-red/20 border border-brand-red/40 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                </div>

                <p class="text-zinc-500 text-sm">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>

        {{-- Form panel --}}
        <div class="flex-1 flex flex-col min-h-screen bg-zinc-50">
            <div class="flex items-center justify-between px-6 py-5 lg:px-10">
                <a href="{{ route('home') }}" class="lg:hidden">
                    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-9 w-auto">
                </a>
                <a href="{{ route('home') }}" class="hidden lg:inline-flex items-center gap-2 text-sm font-semibold text-zinc-500 hover:text-brand-red transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to shop
                </a>
                <a href="{{ route('products.index') }}" class="text-sm font-semibold text-brand-black hover:text-brand-red transition-colors">Browse products</a>
            </div>

            <div class="flex-1 flex items-center justify-center px-6 pb-10 lg:px-10">
                <div class="w-full max-w-md">
                    @isset($heading)
                    <div class="mb-8 text-center lg:text-left">
                        {{ $heading }}
                    </div>
                    @endisset

                    <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl shadow-zinc-200/60 border border-zinc-100 p-7 sm:p-9">
                        {{ $slot }}
                    </div>

                    <p class="mt-8 text-center text-xs text-zinc-400 leading-relaxed">
                        Need help? <a href="mailto:{{ config('store.support_email') }}" class="text-brand-red hover:underline">{{ config('store.support_email') }}</a>
                        · +91 {{ config('store.support_phone') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
