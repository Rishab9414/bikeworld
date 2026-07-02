@extends('layouts.shop')
@section('title', 'My Addresses')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <a href="{{ route('dashboard') }}" class="text-sm text-orange-600 mb-4 inline-block">← Back</a>
    <h1 class="text-2xl font-bold mb-6">Manage Addresses</h1>
    @if(session('success'))<div class="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>@endif
    @foreach($customer->addresses as $addr)
    <div class="bg-white border rounded-2xl p-4 mb-3">
        <div class="flex justify-between"><span class="font-semibold capitalize">{{ $addr->address_type }}</span>@if($addr->is_default_shipping)<span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded">Default</span>@endif</div>
        <p class="text-sm text-slate-600 mt-2">{{ $addr->full_name }} · {{ $addr->mobile }}</p>
        <p class="text-sm text-slate-600">{{ $addr->fullAddress() }}</p>
    </div>
    @endforeach
    <form action="{{ route('account.addresses.store') }}" method="POST" class="bg-white border rounded-2xl p-6 mt-6 space-y-3">
        @csrf
        <h3 class="font-bold">Add New Address</h3>
        <div class="grid sm:grid-cols-2 gap-3">
            <select name="address_type" class="border rounded-xl px-4 py-2.5"><option value="home">Home</option><option value="office">Office</option><option value="other">Other</option></select>
            <input name="full_name" placeholder="Receiver Name" required class="border rounded-xl px-4 py-2.5">
            <input name="mobile" placeholder="Mobile" required class="border rounded-xl px-4 py-2.5">
            <input name="pincode" placeholder="PIN Code" required class="border rounded-xl px-4 py-2.5">
            <input name="address_line_1" placeholder="Address Line 1" required class="sm:col-span-2 border rounded-xl px-4 py-2.5">
            <input name="city" placeholder="City" required class="border rounded-xl px-4 py-2.5">
            <input name="state" placeholder="State" required class="border rounded-xl px-4 py-2.5">
            <input name="country" value="India" required class="border rounded-xl px-4 py-2.5">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default_shipping" value="1" class="rounded text-orange-600"> Set as default shipping</label>
        <button type="submit" class="bg-orange-600 text-white font-semibold px-6 py-2.5 rounded-xl">Add Address</button>
    </form>
</div>
@endsection
