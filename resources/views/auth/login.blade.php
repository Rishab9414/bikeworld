@extends('layouts.shop')

@section('title', 'Sign In — ' . config('app.name'))

@section('content')
<x-auth-shell active="login" title="Welcome back" subtitle="Sign in to track orders and checkout faster.">
    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5 max-w-md">
        @csrf

        <x-auth-field
            label="Email address"
            name="email"
            type="email"
            placeholder="you@example.com"
            autocomplete="username"
            :autofocus="true"
            required
        >
            <x-slot:icon>
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-auth-field>

        <x-auth-password placeholder="Enter your password" autocomplete="current-password" />

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-zinc-300 text-brand-red focus:ring-brand-red/30">
                <span class="text-sm text-zinc-600">Remember me</span>
            </label>
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand-red hover:text-red-700 transition-colors">
                Forgot password?
            </a>
            @endif
        </div>

        <button type="submit" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm py-3.5 rounded-xl transition-colors shadow-md shadow-brand-red/20">
            Sign in to your account
        </button>
    </form>

    <p class="mt-6 text-sm text-zinc-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-bold text-brand-red hover:text-red-700 transition-colors">Create one free</a>
    </p>
</x-auth-shell>
@endsection
