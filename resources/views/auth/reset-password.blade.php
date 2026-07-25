@extends('layouts.shop')

@section('title', 'Set New Password — ' . config('app.name'))

@section('content')
<x-auth-shell active="forgot" title="Set a new password" subtitle="Choose a strong password for your BikeWorld account.">
    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium" :status="session('status')" />

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5 max-w-md">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-auth-field
            label="Email address"
            name="email"
            type="email"
            placeholder="you@example.com"
            autocomplete="username"
            :value="$email"
            :autofocus="true"
            required
        >
            <x-slot:icon>
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-auth-field>

        <x-auth-password
            name="password"
            label="New password"
            placeholder="Minimum 8 characters"
            autocomplete="new-password"
            required
        />

        <x-auth-password
            name="password_confirmation"
            label="Confirm new password"
            placeholder="Re-enter your password"
            autocomplete="new-password"
            required
        />

        <p class="text-xs text-zinc-500 leading-relaxed">
            Use at least 8 characters. Avoid passwords you’ve used on other sites.
        </p>

        <button type="submit" class="w-full bg-brand-red hover:bg-red-700 text-white font-bold text-sm py-3.5 rounded-xl transition-colors shadow-md shadow-brand-red/20">
            Reset password
        </button>
    </form>

    <p class="mt-6">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-500 hover:text-brand-red transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to sign in
        </a>
    </p>
</x-auth-shell>
@endsection
