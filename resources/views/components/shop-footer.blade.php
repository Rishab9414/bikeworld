@php
    $policyLinks = \App\Http\Controllers\Shop\PageController::links();
    $hasLogo = file_exists(public_path('images/logo.png'));
    $categories = ($menuCategories ?? collect())->take(4);
@endphp

<footer class="bg-brand-black text-zinc-400 border-t-2 border-brand-red">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6 lg:gap-8">

            {{-- Brand + contact --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-2">
                <a href="{{ route('home') }}" class="inline-block mb-2">
                    @if($hasLogo)
                        <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto brightness-0 invert">
                    @else
                        <span class="text-lg font-black"><span class="text-brand-red">BIKE</span><span class="text-white">WORLD</span></span>
                    @endif
                </a>
                <p class="text-xs text-zinc-500 leading-relaxed mb-3 max-w-xs">Premium bike accessories, helmets & riding gear — delivered across India.</p>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    <a href="mailto:{{ config('store.support_email') }}" class="hover:text-brand-red transition-colors">{{ config('store.support_email') }}</a>
                    <a href="tel:+91{{ config('store.support_phone') }}" class="hover:text-brand-red transition-colors">+91 {{ config('store.support_phone') }}</a>
                </div>
            </div>

            {{-- Shop --}}
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-2.5">Shop</h4>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="{{ route('products.index') }}" class="hover:text-white transition-colors {{ request()->routeIs('products.index') && !request('category') ? 'text-brand-red' : '' }}">All Products</a></li>
                    @forelse($categories as $category)
                        <li><a href="{{ route('products.index', ['category' => $category->slug]) }}" class="hover:text-white transition-colors {{ request('category') === $category->slug ? 'text-brand-red' : '' }}">{{ $category->name }}</a></li>
                    @empty
                        <li><a href="{{ route('products.index', ['category' => 'helmet']) }}" class="hover:text-white transition-colors">Helmets</a></li>
                        <li><a href="{{ route('products.index', ['category' => 'gloves']) }}" class="hover:text-white transition-colors">Gloves</a></li>
                    @endforelse
                    <li><a href="{{ route('cart.index') }}" class="hover:text-white transition-colors">Cart</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition-colors {{ request()->routeIs('blog.*') ? 'text-brand-red' : '' }}">Blog</a></li>
                </ul>
            </div>

            {{-- Account --}}
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-2.5">Account</h4>
                <ul class="space-y-1.5 text-xs">
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a></li>
                    @endguest
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition-colors">Dashboard</a></li>
                        <li><a href="{{ route('account.wishlist') }}" class="hover:text-white transition-colors">Wishlist</a></li>
                    @endauth
                    <li><a href="{{ route('orders.index') }}" class="hover:text-white transition-colors">My Orders</a></li>
                </ul>
            </div>

            {{-- Policies --}}
            <div class="col-span-2 sm:col-span-1 lg:col-span-2">
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-2.5">Policies</h4>
                <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                    @foreach($policyLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="hover:text-white transition-colors {{ request()->is('pages/'.$link['slug']) ? 'text-brand-red' : '' }}">{{ $link['title'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="mt-6 pt-5 border-t border-zinc-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-zinc-500">
            <p>&copy; {{ date('Y') }} <span class="text-brand-red font-semibold">BIKE</span><span class="text-white font-semibold">WORLD</span>. All rights reserved.</p>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-zinc-600">Secure:</span>
                <span class="text-zinc-400">Razorpay</span>
                <span class="text-zinc-700">·</span>
                <span class="text-zinc-400">UPI</span>
                <span class="text-zinc-700">·</span>
                <span class="text-zinc-400">COD</span>
            </div>
        </div>
    </div>
</footer>
