@extends('layouts.shop')
@section('title', 'My Addresses')
@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-brand-red font-semibold mb-4 inline-block hover:text-red-700">← Back to Account</a>
    <h1 class="text-2xl font-black text-brand-black mb-6">Manage Addresses</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-100">{{ session('success') }}</div>
    @endif

    @forelse($customer->addresses as $addr)
    <div class="bg-white border border-zinc-100 rounded-2xl p-5 mb-4 shadow-sm" x-data="{ editing: false }">
        <div x-show="!editing">
            <div class="flex flex-wrap justify-between gap-2 mb-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-bold capitalize text-brand-black">{{ $addr->address_type }}</span>
                    @if($addr->is_default_shipping)
                        <span class="text-xs bg-brand-red/10 text-brand-red px-2 py-0.5 rounded-full font-semibold">Default Shipping</span>
                    @endif
                    @if($addr->is_default_billing)
                        <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full font-semibold">Default Billing</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="editing = true" class="text-xs font-semibold text-brand-red hover:underline">Edit</button>
                    @if(!$addr->is_default_shipping)
                    <form action="{{ route('account.addresses.default', $addr) }}" method="POST" class="inline">
                        @csrf @method('PATCH')
                        <input type="hidden" name="type" value="shipping">
                        <button type="submit" class="text-xs font-semibold text-zinc-600 hover:text-brand-black">Set default shipping</button>
                    </form>
                    @endif
                    <form action="{{ route('account.addresses.destroy', $addr) }}" method="POST" class="inline" onsubmit="return confirm('Remove this address?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Delete</button>
                    </form>
                </div>
            </div>
            <p class="text-sm text-zinc-600">{{ $addr->full_name }} · {{ $addr->mobile }}</p>
            <p class="text-sm text-zinc-600 mt-1">{{ $addr->fullAddress() }}</p>
        </div>

        <form x-show="editing" x-cloak action="{{ route('account.addresses.update', $addr) }}" method="POST" class="space-y-3 mt-2">
            @csrf @method('PUT')
            <div class="grid sm:grid-cols-2 gap-3">
                <select name="address_type" class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                    <option value="home" @selected($addr->address_type === 'home')>Home</option>
                    <option value="office" @selected($addr->address_type === 'office')>Office</option>
                    <option value="other" @selected($addr->address_type === 'other')>Other</option>
                </select>
                <input name="full_name" value="{{ $addr->full_name }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="mobile" value="{{ $addr->mobile }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="pincode" value="{{ $addr->pincode }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="address_line_1" value="{{ $addr->address_line_1 }}" required class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="address_line_2" value="{{ $addr->address_line_2 }}" placeholder="Address Line 2" class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="landmark" value="{{ $addr->landmark }}" placeholder="Landmark" class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="city" value="{{ $addr->city }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="state" value="{{ $addr->state }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="country" value="{{ $addr->country }}" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default_shipping" value="1" @checked($addr->is_default_shipping) class="rounded text-brand-red"> Default shipping</label>
            <div class="flex gap-2">
                <button type="submit" class="bg-brand-red text-white font-semibold px-5 py-2 rounded-xl text-sm">Save</button>
                <button type="button" @click="editing = false" class="border border-zinc-200 px-5 py-2 rounded-xl text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @empty
        <p class="text-zinc-500 mb-6">No saved addresses yet.</p>
    @endforelse

    <details class="bg-white border border-zinc-100 rounded-2xl p-6 mt-6 shadow-sm group" {{ $customer->addresses->isEmpty() ? 'open' : '' }}>
        <summary class="font-bold text-brand-black cursor-pointer list-none flex justify-between items-center">
            Add New Address
            <span class="text-zinc-400 group-open:rotate-180 transition-transform">▼</span>
        </summary>
        <form action="{{ route('account.addresses.store') }}" method="POST" class="space-y-3 mt-5">
            @csrf
            <div class="grid sm:grid-cols-2 gap-3">
                <select name="address_type" class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm"><option value="home">Home</option><option value="office">Office</option><option value="other">Other</option></select>
                <input name="full_name" placeholder="Receiver Name" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="mobile" placeholder="Mobile" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="pincode" placeholder="PIN Code" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="address_line_1" placeholder="Address Line 1" required class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="address_line_2" placeholder="Address Line 2 (optional)" class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="landmark" placeholder="Landmark (optional)" class="sm:col-span-2 border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="city" placeholder="City" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="state" placeholder="State" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
                <input name="country" value="India" required class="border border-zinc-200 rounded-xl px-4 py-2.5 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default_shipping" value="1" class="rounded text-brand-red"> Set as default shipping</label>
            <button type="submit" class="bg-brand-red text-white font-semibold px-6 py-2.5 rounded-xl text-sm hover:bg-red-700">Add Address</button>
        </form>
    </details>
</div>
@endsection
