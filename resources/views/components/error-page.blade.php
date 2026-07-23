@props(['code' => '!', 'heading' => 'Something went wrong', 'message' => 'Please try again or return to the homepage.'])

<div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-lg">
        <p class="text-7xl sm:text-8xl font-black text-brand-red/20 mb-4">{{ $code }}</p>
        <h1 class="text-2xl sm:text-3xl font-black text-brand-black mb-3">{{ $heading }}</h1>
        <p class="text-zinc-600 mb-8 leading-relaxed">{{ $message }}</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('home') }}" class="inline-flex justify-center bg-brand-red text-white font-bold px-8 py-3 rounded-xl hover:bg-red-700 transition-colors">Go to Homepage</a>
            <a href="{{ route('products.index') }}" class="inline-flex justify-center border border-zinc-200 text-brand-black font-bold px-8 py-3 rounded-xl hover:border-brand-red hover:text-brand-red transition-colors">Browse Products</a>
        </div>
        <div class="mt-10 pt-8 border-t border-zinc-100">
            <p class="text-xs text-zinc-500 mb-3">Quick links</p>
            <div class="flex flex-wrap justify-center gap-4 text-sm">
                <a href="{{ route('search.index') }}" class="text-brand-red font-semibold hover:underline">Search</a>
                <a href="{{ route('cart.index') }}" class="text-brand-red font-semibold hover:underline">Cart</a>
                <a href="{{ route('blog.index') }}" class="text-brand-red font-semibold hover:underline">Blog</a>
                @auth
                    <a href="{{ route('orders.index') }}" class="text-brand-red font-semibold hover:underline">My Orders</a>
                @else
                    <a href="{{ route('login') }}" class="text-brand-red font-semibold hover:underline">Login</a>
                @endauth
            </div>
        </div>
    </div>
</div>
