@extends('layouts.shop')

@section('title', 'Create Account — ' . config('app.name'))

@section('content')
<x-auth-shell active="register" title="Create your account" subtitle="Join {{ config('app.name') }} — it's free and takes less than a minute.">
    @if(session('error'))
        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 text-brand-red px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4 max-w-md">
        @csrf

        <x-auth-field
            label="Full name"
            name="name"
            type="text"
            placeholder="Enter your full name"
            autocomplete="name"
            :autofocus="true"
            required
        >
            <x-slot:icon>
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </x-slot:icon>
        </x-auth-field>

        <x-auth-field
            label="Mobile number"
            name="mobile"
            type="tel"
            placeholder="10-digit mobile number"
            autocomplete="tel"
            required
        >
            <x-slot:icon>
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </x-slot:icon>
        </x-auth-field>

        <x-auth-field
            label="Email address"
            name="email"
            type="email"
            placeholder="you@example.com"
            autocomplete="username"
            required
        >
            <x-slot:icon>
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-auth-field>

        <x-auth-password label="Password" placeholder="Minimum 8 characters" autocomplete="new-password" />

        <x-auth-password name="password_confirmation" label="Confirm password" placeholder="Re-enter your password" autocomplete="new-password" />

        <p class="text-xs text-zinc-400 leading-relaxed">
            By registering, you agree to our
            <a href="{{ route('pages.show', 'terms-and-conditions') }}" class="text-brand-red hover:underline">Terms & Conditions</a>
            and
            <a href="{{ route('pages.show', 'privacy-policy') }}" class="text-brand-red hover:underline">Privacy Policy</a>.
        </p>

        <button type="submit" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm py-3.5 rounded-xl transition-colors shadow-md shadow-brand-red/20">
            Create my account
        </button>
    </form>

    <p class="mt-6 text-sm text-zinc-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-bold text-brand-red hover:text-red-700 transition-colors">Sign in</a>
    </p>
</x-auth-shell>
@endsection
