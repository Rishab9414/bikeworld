@extends('admin.layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-subtitle', 'Manage customer accounts, wallet & loyalty')

@push('scripts')
<script>
(function () {
    const baseUrl = @json(url('/admin/customers'));
    const tableBody = document.getElementById('master-table-body');
    const searchInput = document.getElementById('master-search');
    const paginationEl = document.getElementById('customers-pagination');
    let currentPage = 1;
    let debounceTimer = null;

    function renderRow(row) {
        const initial = (row.name || '?')[0];
        const avatar = row.profile_image
            ? `<img src="${row.profile_image}" class="w-9 h-9 rounded-full object-cover" alt="">`
            : `<div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">${initial}</div>`;
        const statusClass = row.status === 'active' ? 'bg-emerald-100 text-emerald-700'
            : row.status === 'blocked' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600';

        return `<tr class="hover:bg-slate-50">
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    ${avatar}
                    <div><p class="font-medium text-slate-900">${row.name}</p><p class="text-xs text-slate-400">${row.customer_code}</p></div>
                </div>
            </td>
            <td class="px-5 py-3.5 text-sm">${row.mobile}</td>
            <td class="px-5 py-3.5 text-sm">${row.email}</td>
            <td class="px-5 py-3.5 text-sm">${row.registered_at}</td>
            <td class="px-5 py-3.5 text-sm">${row.last_login}</td>
            <td class="px-5 py-3.5 text-sm text-center">${row.total_orders}</td>
            <td class="px-5 py-3.5 text-sm font-semibold">₹${parseFloat(row.total_spend || 0).toFixed(2)}</td>
            <td class="px-5 py-3.5 text-sm">₹${parseFloat(row.wallet_balance || 0).toFixed(2)}</td>
            <td class="px-5 py-3.5 text-sm">${row.loyalty_points}</td>
            <td class="px-5 py-3.5"><span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full ${statusClass}">${row.status}</span></td>
            <td class="px-5 py-3.5 text-right">
                <a href="/admin/customers/${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg inline-block" title="View">View</a>
            </td>
        </tr>`;
    }

    function showLoading() {
        tableBody.innerHTML = '<tr><td colspan="11" class="px-6 py-12 text-center text-slate-400">Loading customers...</td></tr>';
    }

    function renderPagination(meta) {
        if (!paginationEl) return;
        paginationEl.innerHTML = `
            <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 text-sm text-slate-500">
                <span>Page ${meta.current_page}${meta.to ? ' · showing ' + meta.from + '–' + meta.to : ''}</span>
                <div class="flex gap-2">
                    <button type="button" id="customers-prev" ${meta.prev_page_url ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Previous</button>
                    <button type="button" id="customers-next" ${meta.next_page_url ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Next</button>
                </div>
            </div>`;
        document.getElementById('customers-prev')?.addEventListener('click', () => { if (meta.prev_page_url) { currentPage--; loadCustomers(); } });
        document.getElementById('customers-next')?.addEventListener('click', () => { if (meta.next_page_url) { currentPage++; loadCustomers(); } });
    }

    async function loadCustomers() {
        showLoading();
        const params = new URLSearchParams({ page: String(currentPage) });
        if (searchInput?.value.trim()) params.set('search', searchInput.value.trim());
        ['status', 'verified', 'customer_type', 'loyalty_tier', 'date_from', 'date_to'].forEach(k => {
            const el = document.getElementById('filter-' + k.replace('_', '-'));
            if (el?.value) params.set(k, el.value);
        });

        try {
            const res = await fetch(`${baseUrl}/data?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await res.json();
            const payload = result.data || {};
            const rows = Array.isArray(payload.data) ? payload.data : [];

            if (!rows.length) {
                tableBody.innerHTML = '<tr><td colspan="11" class="px-6 py-12 text-center text-slate-400">No customers found.</td></tr>';
            } else {
                tableBody.innerHTML = rows.map(renderRow).join('');
            }

            renderPagination({
                current_page: payload.current_page || 1,
                from: payload.from,
                to: payload.to,
                prev_page_url: payload.prev_page_url,
                next_page_url: payload.next_page_url,
            });
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="11" class="px-6 py-8 text-center text-red-500">Failed to load customers.</td></tr>';
        }
    }

    document.getElementById('apply-filters')?.addEventListener('click', () => { currentPage = 1; loadCustomers(); });
    document.getElementById('reset-filters')?.addEventListener('click', () => {
        document.querySelectorAll('[id^="filter-"]').forEach(el => el.value = '');
        if (searchInput) searchInput.value = '';
        currentPage = 1;
        loadCustomers();
    });
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => { currentPage = 1; loadCustomers(); }, 350);
    });

    loadCustomers();
})();
</script>
@endpush

@section('content')
<div class="mb-4 flex flex-wrap gap-3 justify-between items-center">
    <a href="{{ route('admin.customers.export') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-slate-50">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </a>
    <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Customer
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-6">
    <div class="p-5 border-b border-slate-100">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <input type="text" id="master-search" placeholder="Name, email, mobile, code..." class="admin-input text-sm py-2.5">
            <select id="filter-status" class="admin-input text-sm py-2.5">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="blocked">Blocked</option>
            </select>
            <select id="filter-verified" class="admin-input text-sm py-2.5">
                <option value="">Verified (Any)</option>
                <option value="yes">Verified</option>
                <option value="no">Not Verified</option>
            </select>
            <select id="filter-loyalty-tier" class="admin-input text-sm py-2.5">
                <option value="">All Tiers</option>
                @foreach(['bronze','silver','gold','platinum'] as $tier)
                <option value="{{ $tier }}">{{ ucfirst($tier) }}</option>
                @endforeach
            </select>
            <input type="date" id="filter-date-from" class="admin-input text-sm py-2.5" title="Registration from">
            <input type="date" id="filter-date-to" class="admin-input text-sm py-2.5" title="Registration to">
            <select id="filter-customer-type" class="admin-input text-sm py-2.5">
                <option value="">All Types</option>
                <option value="regular">Regular</option>
                <option value="vip">VIP</option>
                <option value="wholesale">Wholesale</option>
            </select>
            <div class="flex gap-2">
                <button type="button" id="apply-filters" class="flex-1 bg-indigo-600 text-white text-sm font-semibold py-2.5 rounded-xl">Apply</button>
                <button type="button" id="reset-filters" class="px-4 text-sm text-slate-600 border border-slate-200 rounded-xl">Reset</button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1100px]">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Mobile</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Registered</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Last Login</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Orders</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Total Spend</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Wallet</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Points</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="master-table-body"><tr><td colspan="11" class="px-6 py-12 text-center text-slate-400">Loading customers...</td></tr></tbody>
        </table>
    </div>
    <div id="customers-pagination"></div>
</div>
@endsection
