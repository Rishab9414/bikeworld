@extends('admin.layouts.app')
@php $c = $customer; @endphp
@section('title', $c->full_name)
@section('page-title', $c->full_name)
@section('page-subtitle', $c->customer_code.' · '.$c->email)

@section('content')
<div x-data="{ tab: 'overview', showWallet: false, showLoyalty: false, showPassword: false }">
    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @if($c->profileImageUrl())
                <img src="{{ $c->profileImageUrl() }}" class="w-16 h-16 rounded-2xl object-cover">
                @else
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center text-2xl font-bold text-indigo-600">{{ strtoupper(substr($c->first_name,0,1)) }}</div>
                @endif
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $c->full_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $c->country_code }} {{ $c->mobile }} · {{ ucfirst($c->loyalty_tier) }} Tier</p>
                    <span class="inline-flex mt-1 px-2 py-0.5 text-xs font-semibold rounded-full {{ $c->account_status==='active'?'bg-emerald-100 text-emerald-700':($c->account_status==='blocked'?'bg-red-100 text-red-700':'bg-slate-100 text-slate-600') }}">{{ ucfirst($c->account_status) }}</span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.customers.edit', $c) }}" class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-xl">Edit Profile</a>
                <button type="button" @click="showPassword=true" class="px-4 py-2 text-sm font-semibold border border-slate-200 rounded-xl">Reset Password</button>
                <button type="button" id="toggle-block-btn" data-id="{{ $c->id }}" class="px-4 py-2 text-sm font-semibold {{ $c->account_status==='blocked'?'bg-emerald-50 text-emerald-700':'bg-red-50 text-red-700' }} rounded-xl">{{ $c->account_status==='blocked'?'Unblock':'Block' }} Customer</button>
                <button type="button" @click="showWallet=true" class="px-4 py-2 text-sm font-semibold border border-slate-200 rounded-xl">Wallet</button>
                <button type="button" @click="showLoyalty=true" class="px-4 py-2 text-sm font-semibold border border-slate-200 rounded-xl">Loyalty Points</button>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
        @foreach([
            ['label'=>'Total Orders','value'=>$stats['total_orders']],
            ['label'=>'Completed','value'=>$stats['completed_orders']],
            ['label'=>'Pending','value'=>$stats['pending_orders']],
            ['label'=>'Total Spend','value'=>'₹'.number_format($stats['total_spend'],2)],
            ['label'=>'Wallet','value'=>'₹'.number_format($stats['wallet_balance'],2)],
            ['label'=>'Loyalty Points','value'=>$stats['loyalty_points']],
        ] as $stat)
        <div class="bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
            <p class="text-xs text-slate-500 uppercase font-semibold">{{ $stat['label'] }}</p>
            <p class="text-lg font-bold text-slate-900 mt-1">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex border-b border-slate-100 overflow-x-auto">
            @foreach(['overview'=>'Overview','orders'=>'Orders','addresses'=>'Addresses','wishlist'=>'Wishlist','cart'=>'Cart','reviews'=>'Reviews','wallet'=>'Wallet','loyalty'=>'Loyalty','activity'=>'Activity'] as $k=>$l)
            <button type="button" @click="tab='{{ $k }}'" :class="tab==='{{ $k }}'?'border-indigo-600 text-indigo-600 bg-indigo-50/50':'text-slate-500'" class="px-5 py-3 text-sm font-semibold border-b-2 whitespace-nowrap">{{ $l }}</button>
            @endforeach
        </div>
        <div class="p-6">
            <div x-show="tab==='overview'">
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-900">Personal Details</h4>
                        <dl class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ $c->email }} @if($c->email_verified)<span class="text-emerald-600 text-xs">✓</span>@endif</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Mobile</dt><dd>{{ $c->country_code }} {{ $c->mobile }} @if($c->mobile_verified)<span class="text-emerald-600 text-xs">✓</span>@endif</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">DOB</dt><dd>{{ $c->date_of_birth?->format('M d, Y') ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Anniversary</dt><dd>{{ $c->anniversary_date?->format('M d, Y') ?? '—' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Gender</dt><dd>{{ ucfirst($c->gender ?? '—') }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Referral Code</dt><dd class="font-mono">{{ $c->referral_code }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Registered</dt><dd>{{ $c->created_at->format('M d, Y') }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Last Login</dt><dd>{{ $c->last_login?->format('M d, Y H:i') ?? 'Never' }}</dd></div>
                        </dl>
                        <div class="flex gap-2 pt-2">
                            @unless($c->email_verified)<button type="button" class="verify-email text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg">Verify Email</button>@endunless
                            @unless($c->mobile_verified)<button type="button" class="verify-mobile text-xs bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg">Verify Mobile</button>@endunless
                        </div>
                    </div>
                    <div class="space-y-3">
                        <h4 class="font-bold text-slate-900">Statistics</h4>
                        <dl class="text-sm space-y-2">
                            <div class="flex justify-between"><dt class="text-slate-500">Avg Order Value</dt><dd>₹{{ number_format($stats['average_order_value'], 2) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Cancelled Orders</dt><dd>{{ $stats['cancelled_orders'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Wishlist Items</dt><dd>{{ $stats['wishlist_count'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Cart Items</dt><dd>{{ $stats['cart_items'] }}</dd></div>
                            <div class="flex justify-between"><dt class="text-slate-500">Reviews</dt><dd>{{ $stats['reviews_count'] }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>

            <div x-show="tab==='orders'" x-cloak>
                @if($orders->isEmpty())<p class="text-slate-400 text-sm">No orders yet.</p>
                @else
                <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2">Order #</th><th class="pb-2">Date</th><th class="pb-2">Amount</th><th class="pb-2">Status</th></tr></thead>
                <tbody>@foreach($orders as $o)<tr class="border-b border-slate-50"><td class="py-3 font-medium">{{ $o->order_number }}</td><td>{{ $o->created_at->format('M d, Y') }}</td><td>₹{{ number_format($o->total,2) }}</td><td><span class="px-2 py-0.5 rounded-full text-xs bg-slate-100">{{ $o->statusLabel() }}</span></td></tr>@endforeach</tbody></table>
                @endif
            </div>

            <div x-show="tab==='addresses'" x-cloak>
                @forelse($c->addresses as $addr)
                <div class="border border-slate-100 rounded-xl p-4 mb-3">
                    <div class="flex justify-between"><span class="font-semibold capitalize">{{ $addr->address_type }}</span>
                        @if($addr->is_default_shipping)<span class="text-xs bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded">Default Shipping</span>@endif
                    </div>
                    <p class="text-sm text-slate-600 mt-2">{{ $addr->fullAddress() }}</p>
                </div>
                @empty<p class="text-slate-400 text-sm">No addresses saved.</p>@endforelse
            </div>

            <div x-show="tab==='wishlist'" x-cloak>
                @forelse($c->wishlists as $w)
                <div class="flex justify-between py-2 border-b border-slate-50 text-sm"><span>{{ $w->product?->name }}</span><span class="text-slate-400">{{ $w->created_at->format('M d, Y') }}</span></div>
                @empty<p class="text-slate-400 text-sm">Wishlist is empty.</p>@endforelse
            </div>

            <div x-show="tab==='cart'" x-cloak>
                @forelse($c->cartItems as $item)
                <div class="flex justify-between py-2 border-b border-slate-50 text-sm"><span>{{ $item->product?->name }} × {{ $item->quantity }}</span><span>₹{{ number_format($item->subtotal(),2) }}</span></div>
                @empty<p class="text-slate-400 text-sm">Cart is empty.</p>@endforelse
            </div>

            <div x-show="tab==='reviews'" x-cloak>
                @forelse($c->reviews as $r)
                <div class="border-b border-slate-50 py-3"><div class="flex justify-between"><span class="font-medium">{{ $r->product?->name }}</span><span>{{ str_repeat('★',$r->rating) }}</span></div><p class="text-sm text-slate-600 mt-1">{{ $r->review }}</p></div>
                @empty<p class="text-slate-400 text-sm">No reviews yet.</p>@endforelse
            </div>

            <div x-show="tab==='wallet'" x-cloak>
                <p class="text-2xl font-bold mb-4">₹{{ number_format($stats['wallet_balance'], 2) }}</p>
                @forelse($c->wallet?->transactions ?? [] as $tx)
                <div class="flex justify-between py-2 text-sm border-b border-slate-50"><span class="{{ $tx->transaction_type==='credit'?'text-emerald-600':'text-red-600' }}">{{ ucfirst($tx->transaction_type) }} ₹{{ $tx->amount }}</span><span class="text-slate-400">{{ $tx->transaction_date->format('M d, Y') }}</span></div>
                @empty<p class="text-slate-400 text-sm">No transactions.</p>@endforelse
            </div>

            <div x-show="tab==='loyalty'" x-cloak>
                <p class="text-2xl font-bold mb-1">{{ $stats['loyalty_points'] }} pts</p>
                <p class="text-sm text-slate-500 mb-4">Lifetime: {{ $c->loyaltyPoint?->lifetime_points ?? 0 }} · Redeemed: {{ $c->loyaltyPoint?->redeemed_points ?? 0 }}</p>
                @forelse($c->loyaltyPoint?->transactions ?? [] as $tx)
                <div class="flex justify-between py-2 text-sm border-b border-slate-50"><span>{{ $tx->remarks ?? ucfirst($tx->transaction_type) }}</span><span class="{{ $tx->points>0?'text-emerald-600':'text-red-600' }}">{{ $tx->points>0?'+':'' }}{{ $tx->points }}</span></div>
                @empty<p class="text-slate-400 text-sm">No loyalty transactions.</p>@endforelse
            </div>

            <div x-show="tab==='activity'" x-cloak>
                @forelse($c->loginLogs as $log)
                <div class="flex justify-between py-2 text-sm border-b border-slate-50"><span>{{ $log->ip_address }} · {{ $log->device_type ?? 'Unknown' }}</span><span class="text-slate-400">{{ $log->logged_in_at->format('M d, Y H:i') }}</span></div>
                @empty<p class="text-slate-400 text-sm">No login history.</p>@endforelse
            </div>
        </div>
    </div>

    {{-- Wallet Modal --}}
    <div x-show="showWallet" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md p-6" @click.outside="showWallet=false">
            <h3 class="font-bold text-lg mb-4">Adjust Wallet</h3>
            <form id="wallet-form" class="space-y-3">
                <select name="type" class="admin-input text-sm"><option value="credit">Add Credit</option><option value="debit">Deduct</option></select>
                <input name="amount" type="number" step="0.01" min="0.01" required placeholder="Amount (₹)" class="admin-input text-sm">
                <input name="description" required placeholder="Remarks" class="admin-input text-sm">
                <div class="flex justify-end gap-2"><button type="button" @click="showWallet=false" class="px-4 py-2 text-sm rounded-xl border">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl">Save</button></div>
            </form>
        </div>
    </div>

    {{-- Loyalty Modal --}}
    <div x-show="showLoyalty" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md p-6" @click.outside="showLoyalty=false">
            <h3 class="font-bold text-lg mb-4">Adjust Loyalty Points</h3>
            <form id="loyalty-form" class="space-y-3">
                <select name="type" class="admin-input text-sm"><option value="add">Add Points</option><option value="deduct">Deduct Points</option></select>
                <input name="points" type="number" min="1" required placeholder="Points" class="admin-input text-sm">
                <input name="remarks" required placeholder="Remarks" class="admin-input text-sm">
                <div class="flex justify-end gap-2"><button type="button" @click="showLoyalty=false" class="px-4 py-2 text-sm rounded-xl border">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl">Save</button></div>
            </form>
        </div>
    </div>

    {{-- Password Modal --}}
    <div x-show="showPassword" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md p-6" @click.outside="showPassword=false">
            <h3 class="font-bold text-lg mb-4">Reset Password</h3>
            <form id="password-form" class="space-y-3">
                <input name="password" type="password" required placeholder="New password" class="admin-input text-sm">
                <input name="password_confirmation" type="password" required placeholder="Confirm password" class="admin-input text-sm">
                <div class="flex justify-end gap-2"><button type="button" @click="showPassword=false" class="px-4 py-2 text-sm rounded-xl border">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-xl">Reset</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
const base = @js(url('/admin/customers/'.$c->id));
const req = async (url, method, body) => {
    const r = await fetch(url, { method, headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }, body: JSON.stringify(body) });
    const j = await r.json();
    if (!r.ok) throw new Error(j.message || 'Request failed');
    return j;
};

document.getElementById('toggle-block-btn')?.addEventListener('click', async () => {
    await req(`${base}/toggle-block`, 'PATCH', {});
    location.reload();
});
document.querySelector('.verify-email')?.addEventListener('click', async () => { await req(`${base}/verify-email`, 'PATCH', {}); location.reload(); });
document.querySelector('.verify-mobile')?.addEventListener('click', async () => { await req(`${base}/verify-mobile`, 'PATCH', {}); location.reload(); });
document.getElementById('wallet-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target));
    await req(`${base}/wallet`, 'POST', fd);
    location.reload();
});
document.getElementById('loyalty-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target));
    await req(`${base}/loyalty`, 'POST', fd);
    location.reload();
});
document.getElementById('password-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = Object.fromEntries(new FormData(e.target));
    await req(`${base}/reset-password`, 'POST', fd);
    alert('Password reset successfully');
    location.reload();
});
</script>
@endpush
