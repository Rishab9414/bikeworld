@extends('admin.layouts.app')

@section('title', 'Payment Settings')
@section('page-title', 'Payment Settings')
@section('page-subtitle', 'Control payment options shown at checkout')

@section('content')
<form action="{{ route('admin.settings.payments.update') }}" method="POST" class="max-w-2xl">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-6">
        <div>
            <h3 class="font-bold text-lg text-slate-900 mb-1">Cash on Delivery (COD)</h3>
            <p class="text-sm text-slate-500 mb-4">When enabled, customers can choose COD at checkout. When disabled, only online payment (Razorpay) is available.</p>

            <label class="flex items-start gap-4 p-4 rounded-xl border border-slate-200 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/50">
                <input type="hidden" name="cod_enabled" value="0">
                <input type="checkbox" name="cod_enabled" value="1" @checked(old('cod_enabled', $codEnabled))
                    class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <div>
                    <p class="font-semibold text-slate-900">Enable Cash on Delivery</p>
                    <p class="text-sm text-slate-500 mt-0.5">Show COD as a payment option on the storefront checkout page</p>
                </div>
            </label>
        </div>

        <div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm text-slate-600">
            <p class="font-medium text-slate-700 mb-1">Current status</p>
            <p>Cash on Delivery is <span class="font-semibold {{ $codEnabled ? 'text-emerald-700' : 'text-red-600' }}">{{ $codEnabled ? 'enabled' : 'disabled' }}</span> for customers.</p>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl">Save Settings</button>
    </div>
</form>
@endsection
