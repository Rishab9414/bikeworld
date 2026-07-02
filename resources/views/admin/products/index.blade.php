@extends('admin.layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Manage your product catalog')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5"><div class="flex items-center gap-3">
        ${row.primary_image ? `<img src="/storage/${row.primary_image}" class="w-10 h-10 rounded-lg object-cover">` : `<div class="w-10 h-10 bg-slate-100 rounded-lg"></div>`}
        <div><p class="font-medium text-slate-900">${row.name}</p><p class="text-xs text-slate-400">${row.sku || '—'}</p></div>
    </div></td>
    <td class="px-5 py-3.5 text-sm">${row.category || '—'}</td>
    <td class="px-5 py-3.5 text-sm">${row.brand || '—'}</td>
    <td class="px-5 py-3.5 font-semibold">₹${parseFloat(row.selling_price||0).toFixed(2)}</td>
    <td class="px-5 py-3.5 text-sm">${row.stock}</td>
    <td class="px-5 py-3.5"><span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full ${row.status==='published'?'bg-emerald-100 text-emerald-700':row.status==='draft'?'bg-slate-100 text-slate-600':'bg-amber-100 text-amber-700'}">${row.status}</span></td>
    <td class="px-5 py-3.5 text-right"><div class="flex justify-end gap-1">
        <a href="/admin/products/${row.id}/edit" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
        <button data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div></td>
</tr>`;

class ProductListCrud extends MasterCrud {
    async deleteRecord(id) {
        if (!confirm('Delete this product?')) return;
        try {
            const result = await this.request(`${this.baseUrl}/${id}`, { method: 'DELETE' });
            this.showToast(result.message);
            this.loadData();
        } catch (e) { this.showToast(e.message, 'error'); }
    }
    async loadData() {
        const params = new URLSearchParams();
        if (this.searchInput?.value) params.set('search', this.searchInput.value);
        if (this.statusFilter?.value) params.set('status', this.statusFilter.value);
        this.tableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>`;
        try {
            const result = await this.request(`${this.baseUrl}/data?${params}`);
            const rows = result.data.data || result.data;
            this.renderTable(Array.isArray(rows) ? rows : []);
        } catch (e) {
            this.tableBody.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load.</td></tr>`;
        }
    }
}
new ProductListCrud({ baseUrl: @js(url('/admin/products')), module: 'products' });
</script>
@endpush

@section('content')
<div class="mb-4 flex justify-end">
    <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Product
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 border-b border-slate-100">
        <div class="flex flex-1 gap-3">
            <input type="text" id="master-search" placeholder="Search name, SKU..." class="admin-input max-w-xs text-sm py-2.5">
            <select id="master-status-filter" class="admin-input max-w-[140px] text-sm py-2.5">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Product</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Category</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Brand</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Price</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Stock</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody id="master-table-body"><tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr></tbody>
        </table>
    </div>
</div>
<div id="master-toast" class="hidden fixed top-4 right-4 z-[100]"></div>
@endsection
