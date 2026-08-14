@extends('admin.layouts.app')
@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'Manage orders, manual shipping & tracking')

@push('scripts')
<script>
(function () {
    const baseUrl = @json(url('/admin/orders'));
    const tableBody = document.getElementById('master-table-body');
    const searchInput = document.getElementById('master-search');
    const statusEl = document.getElementById('filter-status');
    const payEl = document.getElementById('filter-payment-status');
    const dateFromEl = document.getElementById('filter-date-from');
    const dateToEl = document.getElementById('filter-date-to');
    const paginationEl = document.getElementById('orders-pagination');
    let currentPage = 1;
    let debounceTimer = null;

    function renderRow(row) {
        return `<tr class="hover:bg-slate-50">
            <td class="px-5 py-3.5 font-medium text-indigo-600">${row.order_number}</td>
            <td class="px-5 py-3.5"><p class="font-medium text-slate-900">${row.customer_name}</p><p class="text-xs text-slate-400">${row.mobile}</p></td>
            <td class="px-5 py-3.5 text-sm">${row.email}</td>
            <td class="px-5 py-3.5 text-sm text-center">${row.items_count}</td>
            <td class="px-5 py-3.5 font-semibold">₹${parseFloat(row.grand_total || 0).toFixed(2)}</td>
            <td class="px-5 py-3.5"><span class="px-2 py-0.5 text-xs font-semibold rounded-full ${row.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}">${row.payment_status}</span></td>
            <td class="px-5 py-3.5"><span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">${row.status.replace(/_/g, ' ')}</span></td>
            <td class="px-5 py-3.5 text-sm">${row.created_at}</td>
            <td class="px-5 py-3.5 text-right"><a href="/admin/orders/${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg inline-block">View</a></td>
        </tr>`;
    }

    function showLoading() {
        tableBody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">Loading orders...</td></tr>';
    }

    function renderPagination(meta) {
        if (!paginationEl) return;
        const hasPrev = meta.prev_page_url;
        const hasNext = meta.next_page_url;
        paginationEl.innerHTML = `
            <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 text-sm text-slate-500">
                <span>Page ${meta.current_page}${meta.to ? ' · showing ' + meta.from + '–' + meta.to : ''}</span>
                <div class="flex gap-2">
                    <button type="button" id="orders-prev" ${hasPrev ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Previous</button>
                    <button type="button" id="orders-next" ${hasNext ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Next</button>
                </div>
            </div>`;
        document.getElementById('orders-prev')?.addEventListener('click', () => { if (hasPrev) { currentPage--; loadOrders(); } });
        document.getElementById('orders-next')?.addEventListener('click', () => { if (hasNext) { currentPage++; loadOrders(); } });
    }

    async function loadOrders() {
        showLoading();
        const params = new URLSearchParams({ page: String(currentPage) });
        if (searchInput?.value.trim()) params.set('search', searchInput.value.trim());
        if (statusEl?.value) params.set('status', statusEl.value);
        params.set('payment_status', payEl?.value ?? '');
        if (dateFromEl?.value) params.set('date_from', dateFromEl.value);
        if (dateToEl?.value) params.set('date_to', dateToEl.value);

        try {
            const res = await fetch(`${baseUrl}/data?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await res.json();
            const payload = result.data || {};
            const rows = Array.isArray(payload.data) ? payload.data : (Array.isArray(payload) ? payload : []);

            if (!rows.length) {
                tableBody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">No orders found.</td></tr>';
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
            tableBody.innerHTML = '<tr><td colspan="9" class="px-6 py-8 text-center text-red-500">Failed to load orders.</td></tr>';
        }
    }

    function reloadFromStart() {
        currentPage = 1;
        loadOrders();
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(reloadFromStart, 350);
    });
    statusEl?.addEventListener('change', reloadFromStart);
    payEl?.addEventListener('change', reloadFromStart);
    document.getElementById('apply-filters')?.addEventListener('click', reloadFromStart);
    document.getElementById('reset-filters')?.addEventListener('click', () => {
        if (statusEl) statusEl.value = '';
        if (payEl) payEl.value = '';
        if (dateFromEl) dateFromEl.value = '';
        if (dateToEl) dateToEl.value = '';
        if (searchInput) searchInput.value = '';
        reloadFromStart();
    });

    loadOrders();
})();
</script>
@endpush

@section('content')
<div class="mb-4 flex justify-end">
    <a href="{{ route('admin.orders.sync-payments') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Sync Razorpay Payments
    </a>
</div>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-6">
    <div class="p-5 border-b border-slate-100 grid sm:grid-cols-2 lg:grid-cols-6 gap-3">
        <input type="text" id="master-search" placeholder="Order #, customer..." class="admin-input text-sm py-2.5">
        <select id="filter-status" class="admin-input text-sm py-2.5">
            <option value="">All Order Status</option>
            @foreach(['pending','confirmed','shipment_created','pickup_scheduled','in_transit','out_for_delivery','delivered','completed','cancelled','returned','refunded'] as $st)
            <option value="{{ $st }}">{{ ucwords(str_replace('_',' ',$st)) }}</option>
            @endforeach
        </select>
        <select id="filter-payment-status" class="admin-input text-sm py-2.5">
            <option value="">All Payments</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="refunded">Refunded</option>
        </select>
        <input type="date" id="filter-date-from" class="admin-input text-sm py-2.5" placeholder="From date">
        <input type="date" id="filter-date-to" class="admin-input text-sm py-2.5" title="To date">
        <div class="flex gap-2">
            <button type="button" id="apply-filters" class="flex-1 bg-indigo-600 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-indigo-700">Apply</button>
            <button type="button" id="reset-filters" class="px-3 text-sm text-slate-600 border border-slate-200 py-2.5 rounded-xl hover:bg-slate-50">Reset</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1000px]">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Order #</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Customer</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Items</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Total</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Payment</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="master-table-body"><tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">Loading orders...</td></tr></tbody>
        </table>
    </div>
    <div id="orders-pagination"></div>
</div>
@endsection
