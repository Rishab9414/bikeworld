@extends('admin.layouts.app')

@section('title', 'Tax / GST Settings')
@section('page-title', 'Tax / GST Settings')
@section('page-subtitle', 'Control how GST is applied to product prices and checkout')

@section('content')
<form action="{{ route('admin.settings.tax.update') }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Default price type</h3>
            <p class="text-sm text-slate-500 mb-4">Choose whether product prices entered in admin include GST or not. You can override this per product when editing a product.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50 mb-3">
                <input type="radio" name="default_tax_included" value="0" @checked(!old('default_tax_included', $defaultTaxIncluded))
                    class="mt-1 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">GST exclusive — add at checkout (recommended)</p>
                    <p class="text-sm text-slate-500 mt-0.5">Product price is before GST. GST is calculated and added on cart & checkout.</p>
                    <p class="text-xs text-slate-400 mt-1">Example: ₹1,000 + 18% GST = ₹1,180 total</p>
                </div>
            </label>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="radio" name="default_tax_included" value="1" @checked(old('default_tax_included', $defaultTaxIncluded))
                    class="mt-1 border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">GST inclusive — already in price</p>
                    <p class="text-sm text-slate-500 mt-0.5">Product price already includes GST. No extra GST is added at checkout.</p>
                    <p class="text-xs text-slate-400 mt-1">Example: MRP ₹1,180 (includes GST) — customer pays ₹1,180</p>
                </div>
            </label>
        </div>

        <div class="rounded-xl bg-amber-50 border border-amber-100 px-4 py-3 text-sm text-amber-900">
            <p class="font-medium mb-1">Per-product control</p>
            <p>When adding/editing a product, use the <strong>Tax Included in Price</strong> checkbox and assign a <strong>Tax / GST</strong> rate (18%, 12%, etc.) from Master Data → Tax / GST.</p>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">Save Settings</button>
    </div>
</form>
@endsection
