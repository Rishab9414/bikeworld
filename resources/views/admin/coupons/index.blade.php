@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('page-title', 'Coupons')
@section('page-subtitle', 'Discount codes for cart & checkout')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <p class="text-sm text-slate-500">{{ $coupons->count() }} coupon(s)</p>
    <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Coupon
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Code</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Discount</th>
                <th class="px-5 py-3 text-left font-semibold text-slate-600">Rules</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Used</th>
                <th class="px-5 py-3 text-center font-semibold text-slate-600">Status</th>
                <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($coupons as $coupon)
            <tr class="hover:bg-slate-50">
                <td class="px-5 py-3">
                    <p class="font-black text-slate-900 tracking-wide">{{ $coupon->code }}</p>
                    @if($coupon->description)<p class="text-xs text-slate-400 mt-0.5">{{ $coupon->description }}</p>@endif
                </td>
                <td class="px-5 py-3">
                    <span class="font-semibold text-indigo-600">{{ $coupon->valueLabel() }}</span>
                    <p class="text-xs text-slate-400">{{ $coupon->typeLabel() }}</p>
                </td>
                <td class="px-5 py-3 text-xs text-slate-500 space-y-0.5">
                    @if($coupon->min_order_amount)<p>Min order: ₹{{ number_format($coupon->min_order_amount, 2) }}</p>@endif
                    @if($coupon->category)<p>Category: {{ $coupon->category->name }}</p>@endif
                    @if($coupon->usage_limit)<p>Limit: {{ $coupon->usages_count }}/{{ $coupon->usage_limit }}</p>@endif
                    @if($coupon->expires_at)<p>Expires: {{ $coupon->expires_at->format('d M Y') }}</p>@endif
                </td>
                <td class="px-5 py-3 text-center text-slate-600">{{ $coupon->usages_count }}</td>
                <td class="px-5 py-3 text-center">
                    @php $status = $coupon->adminStatus(); @endphp
                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $status['class'] }}">
                        {{ $status['label'] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No coupons yet. Create your first discount code.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
