@extends('admin.layouts.app')
@php $isEdit = $coupon->exists; @endphp
@section('title', $isEdit ? 'Edit Coupon' : 'Add Coupon')
@section('page-title', $isEdit ? 'Edit Coupon' : 'Add Coupon')
@section('page-subtitle', 'Customers apply this code at cart or checkout')

@section('content')
<form action="{{ $isEdit ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" method="POST" class="max-w-3xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Coupon Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required maxlength="50" placeholder="SAVE10" class="w-full rounded-xl border-slate-200 uppercase focus:border-indigo-500 focus:ring-indigo-500">
                @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <input type="text" name="description" value="{{ old('description', $coupon->description) }}" placeholder="New user offer" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Discount Type <span class="text-red-500">*</span></label>
                <select name="type" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed amount (₹)</option>
                    <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percentage (%)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Value <span class="text-red-500">*</span></label>
                <input type="number" name="value" value="{{ old('value', $coupon->value) }}" step="0.01" min="0.01" required class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <p class="text-xs text-slate-400 mt-1">₹ amount or % off eligible cart total.</p>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Minimum Order Amount</label>
                <input type="number" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" step="0.01" min="0" placeholder="Optional" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Max Discount (for % coupons)</label>
                <input type="number" name="max_discount" value="{{ old('max_discount', $coupon->max_discount) }}" step="0.01" min="0" placeholder="Optional" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Total Usage Limit</label>
                <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1" placeholder="Unlimited" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Uses Per Customer</label>
                <input type="number" name="usage_per_customer" value="{{ old('usage_per_customer', $coupon->usage_per_customer ?? 1) }}" min="1" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Category Restriction</label>
            <select name="category_id" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $coupon->category_id) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-400 mt-1">If selected, discount applies only to products in this category.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Starts At</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Expires At</label>
                <input type="datetime-local" name="expires_at" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border-slate-200 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon->is_active)) class="rounded text-indigo-600">
            Active
        </label>
        <p class="text-xs text-slate-400">Expiry date is valid until end of that day (11:59 PM).</p>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">{{ $isEdit ? 'Update Coupon' : 'Create Coupon' }}</button>
        <a href="{{ route('admin.coupons.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">Cancel</a>
    </div>
</form>
@endsection
