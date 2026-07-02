@extends('admin.layouts.app')
@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'Manage orders, shipments & Delhivery tracking')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-indigo-600">${row.order_number}</td>
    <td class="px-5 py-3.5"><p class="font-medium text-slate-900">${row.customer_name}</p><p class="text-xs text-slate-400">${row.mobile}</p></td>
    <td class="px-5 py-3.5 text-sm">${row.email}</td>
    <td class="px-5 py-3.5 text-sm text-center">${row.items_count}</td>
    <td class="px-5 py-3.5 font-semibold">₹${parseFloat(row.grand_total||0).toFixed(2)}</td>
    <td class="px-5 py-3.5"><span class="px-2 py-0.5 text-xs font-semibold rounded-full ${row.payment_status==='paid'?'bg-emerald-100 text-emerald-700':'bg-amber-100 text-amber-700'}">${row.payment_status}</span></td>
    <td class="px-5 py-3.5"><span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">${row.status.replace(/_/g,' ')}</span></td>
    <td class="px-5 py-3.5 text-sm">${row.created_at}</td>
    <td class="px-5 py-3.5 text-right"><a href="/admin/orders/${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg inline-block">View</a></td>
</tr>`;

class OrderListCrud extends MasterCrud {
    constructor(config) {
        super(config);
        this.applyDateFilter = false;
    }

    async loadData() {
        const params = new URLSearchParams();
        if (this.searchInput?.value) params.set('search', this.searchInput.value);
        const statusEl = document.getElementById('filter-status');
        const payEl = document.getElementById('filter-payment-status');
        if (statusEl?.value) params.set('status', statusEl.value);
        if (payEl?.value) params.set('payment_status', payEl.value);
        if (this.applyDateFilter) {
            const from = document.getElementById('filter-date-from')?.value;
            const to = document.getElementById('filter-date-to')?.value;
            if (from) params.set('date_from', from);
            if (to) params.set('date_to', to);
        }
        this.tableBody.innerHTML = `<tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>`;
        try {
            const result = await this.request(`${this.baseUrl}/data?${params}`);
            const rows = result.data.data || result.data;
            this.renderTable(Array.isArray(rows) ? rows : []);
        } catch (e) {
            this.tableBody.innerHTML = `<tr><td colspan="9" class="px-6 py-8 text-center text-red-500">Failed to load.</td></tr>`;
        }
    }
}
const crud = new OrderListCrud({ baseUrl: @js(url('/admin/orders')), module: 'orders' });
document.getElementById('apply-filters')?.addEventListener('click', () => {
    crud.applyDateFilter = true;
    crud.loadData();
});
document.getElementById('reset-filters')?.addEventListener('click', () => {
    crud.applyDateFilter = false;
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-payment-status').value = '';
    document.getElementById('filter-date-from').value = new Date().toISOString().slice(0, 10);
    document.getElementById('filter-date-to').value = new Date().toISOString().slice(0, 10);
    if (crud.searchInput) crud.searchInput.value = '';
    crud.loadData();
});
</script>
@endpush

@section('content')
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
            <option value="">Payment Status</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="refunded">Refunded</option>
        </select>
        <input type="date" id="filter-date-from" value="{{ now()->format('Y-m-d') }}" class="admin-input text-sm py-2.5">
        <input type="date" id="filter-date-to" value="{{ now()->format('Y-m-d') }}" class="admin-input text-sm py-2.5" title="To date">
        <button type="button" id="apply-filters" class="bg-indigo-600 text-white text-sm font-semibold py-2.5 rounded-xl">Apply Filters</button>
        <button type="button" id="reset-filters" class="text-sm text-slate-600 border border-slate-200 py-2.5 rounded-xl">Reset</button>
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
            <tbody id="master-table-body"><tr><td colspan="9" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr></tbody>
        </table>
    </div>
</div>
<div id="master-toast" class="hidden fixed top-4 right-4 z-[100]"></div>
@endsection
