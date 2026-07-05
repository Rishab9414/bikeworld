@props([
    'active' => 'login',
    'title' => 'Welcome back',
    'subtitle' => 'Sign in to your account',
])

<section class="bg-zinc-50 pb-16 lg:pb-20">
    {{-- Breadcrumb --}}
    <div class="bg-white border-b border-zinc-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center gap-2 text-xs sm:text-sm text-zinc-500">
                <a href="{{ route('home') }}" class="hover:text-brand-red transition-colors">Home</a>
                <svg class="w-3.5 h-3.5 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-brand-black font-semibold">{{ $active === 'register' ? 'Create Account' : ($active === 'forgot' ? 'Reset Password' : 'Sign In') }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-10 lg:pt-14">
        <div class="grid lg:grid-cols-5 bg-white rounded-2xl lg:rounded-3xl shadow-xl shadow-zinc-200/70 border border-zinc-100 overflow-hidden min-h-[560px]">

            {{-- Promo panel --}}
            <div class="lg:col-span-2 relative bg-brand-black text-white overflow-hidden">
                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=900&q=80" alt="" class="absolute inset-0 w-full h-full object-cover opacity-40" aria-hidden="true">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/80 to-brand-black/60"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-red/30 via-transparent to-transparent"></div>

                <div class="relative z-10 p-8 sm:p-10 lg:p-12 flex flex-col justify-between h-full min-h-[220px] lg:min-h-full">
                    <div>
                        <a href="{{ route('home') }}" class="inline-block mb-8 lg:mb-12">
                            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 lg:h-11 w-auto brightness-0 invert">
                        </a>
                        <span class="inline-flex items-center gap-2 bg-brand-red/20 border border-brand-red/40 text-brand-red text-[10px] sm:text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full">
                            <span class="w-1.5 h-1.5 bg-brand-red rounded-full animate-pulse"></span>
                            India's Bike Store
                        </span>
                        <h2 class="mt-5 text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight">
                            Gear up for<br>every ride.
                        </h2>
                        <p class="mt-4 text-zinc-300 text-sm sm:text-base leading-relaxed max-w-xs">
                            Helmets, accessories & parts with fast delivery, COD, and secure online payment.
                        </p>
                    </div>

                    <div class="hidden sm:grid grid-cols-3 gap-3 mt-8 lg:mt-0">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-lg font-black text-white">500+</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5 uppercase tracking-wide">Products</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-lg font-black text-white">COD</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5 uppercase tracking-wide">Available</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10 text-center">
                            <p class="text-lg font-black text-white">24/7</p>
                            <p class="text-[10px] text-zinc-400 mt-0.5 uppercase tracking-wide">Support</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form panel --}}
            <div class="lg:col-span-3 p-6 sm:p-8 lg:p-10 xl:p-12 flex flex-col justify-center">
                @if($active !== 'forgot')
                <div class="flex p-1 bg-zinc-100 rounded-xl mb-8 max-w-sm">
                    <a href="{{ route('login') }}"
                       class="flex-1 text-center py-2.5 text-sm font-bold rounded-lg transition-all duration-200 {{ $active === 'login' ? 'bg-white text-brand-black shadow-sm' : 'text-zinc-500 hover:text-brand-black' }}">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}"
                       class="flex-1 text-center py-2.5 text-sm font-bold rounded-lg transition-all duration-200 {{ $active === 'register' ? 'bg-white text-brand-black shadow-sm' : 'text-zinc-500 hover:text-brand-black' }}">
                        Register
                    </a>
                </div>
                @endif

                <div class="mb-6 sm:mb-8">
                    <h1 class="text-2xl sm:text-3xl font-black text-brand-black tracking-tight">{{ $title }}</h1>
                    <p class="mt-1.5 text-sm text-zinc-500">{{ $subtitle }}</p>
                </div>

                {{ $slot }}

                {{-- Trust strip --}}
                <div class="mt-8 pt-6 border-t border-zinc-100 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-zinc-400">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Secure checkout
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-brand-red" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        +91 {{ config('store.support_phone') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ config('store.support_email') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
