@extends('admin.layouts.app')
@section('title', 'Products')
@section('page-title', 'Products')
@section('page-subtitle', 'Manage your product catalog')

@push('scripts')
<script>
(function () {
    const baseUrl = @json(url('/admin/products'));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const tableBody = document.getElementById('master-table-body');
    const searchInput = document.getElementById('master-search');
    const statusFilter = document.getElementById('master-status-filter');
    const paginationEl = document.getElementById('products-pagination');
    let currentPage = 1;
    let debounceTimer = null;

    function imageCell(path) {
        if (!path) return '<div class="w-10 h-10 bg-slate-100 rounded-lg shrink-0"></div>';
        return `<img src="/storage/${path}" class="w-10 h-10 rounded-lg object-cover shrink-0" alt="" loading="lazy">`;
    }

    function renderRow(row) {
        const statusClass = row.status === 'published' ? 'bg-emerald-100 text-emerald-700'
            : row.status === 'draft' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-700';

        return `<tr class="hover:bg-slate-50">
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    ${imageCell(row.primary_image)}
                    <div class="min-w-0">
                        <p class="font-medium text-slate-900 truncate">${row.name}</p>
                        <p class="text-xs text-slate-400">${row.sku || '—'}</p>
                    </div>
                </div>
            </td>
            <td class="px-5 py-3.5 text-sm">${row.category || '—'}</td>
            <td class="px-5 py-3.5 text-sm">${row.brand || '—'}</td>
            <td class="px-5 py-3.5 font-semibold">₹${parseFloat(row.selling_price || 0).toFixed(2)}</td>
            <td class="px-5 py-3.5 text-sm">${row.stock}</td>
            <td class="px-5 py-3.5"><span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full ${statusClass}">${row.status}</span></td>
            <td class="px-5 py-3.5 text-right">
                <div class="flex justify-end gap-1">
                    <a href="/admin/products/${row.id}/edit" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit">Edit</a>
                    <button type="button" data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete">Del</button>
                </div>
            </td>
        </tr>`;
    }

    function showLoading() {
        tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading products...</td></tr>';
    }

    function showToast(msg, type = 'success') {
        const el = document.getElementById('master-toast');
        if (!el) return;
        el.className = `fixed top-4 right-4 z-[100] px-4 py-3 rounded-xl text-sm font-medium shadow-lg ${type === 'error' ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'}`;
        el.textContent = msg;
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 3000);
    }

    function renderPagination(meta) {
        if (!paginationEl) return;
        paginationEl.innerHTML = `
            <div class="flex items-center justify-between px-5 py-4 border-t border-slate-100 text-sm text-slate-500">
                <span>Page ${meta.current_page}${meta.to ? ' · ' + meta.from + '–' + meta.to : ''}</span>
                <div class="flex gap-2">
                    <button type="button" id="products-prev" ${meta.prev_page_url ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Previous</button>
                    <button type="button" id="products-next" ${meta.next_page_url ? '' : 'disabled'} class="px-3 py-1.5 rounded-lg border border-slate-200 disabled:opacity-40 hover:bg-slate-50">Next</button>
                </div>
            </div>`;
        document.getElementById('products-prev')?.addEventListener('click', () => { if (meta.prev_page_url) { currentPage--; loadProducts(); } });
        document.getElementById('products-next')?.addEventListener('click', () => { if (meta.next_page_url) { currentPage++; loadProducts(); } });
    }

    async function loadProducts() {
        showLoading();
        const params = new URLSearchParams({ page: String(currentPage) });
        if (searchInput?.value.trim()) params.set('search', searchInput.value.trim());
        if (statusFilter?.value) params.set('status', statusFilter.value);

        try {
            const res = await fetch(`${baseUrl}/data?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await res.json();
            const payload = result.data || {};
            const rows = Array.isArray(payload.data) ? payload.data : [];

            if (!rows.length) {
                tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No products found.</td></tr>';
            } else {
                tableBody.innerHTML = rows.map(renderRow).join('');
                tableBody.querySelectorAll('[data-delete]').forEach(btn => {
                    btn.addEventListener('click', () => deleteProduct(btn.dataset.delete));
                });
            }

            renderPagination({
                current_page: payload.current_page || 1,
                from: payload.from,
                to: payload.to,
                prev_page_url: payload.prev_page_url,
                next_page_url: payload.next_page_url,
            });
        } catch (e) {
            tableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Failed to load products.</td></tr>';
        }
    }

    async function deleteProduct(id) {
        if (!confirm('Delete this product?')) return;
        try {
            const res = await fetch(`${baseUrl}/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await res.json();
            if (!res.ok) throw new Error(result.message || 'Delete failed');
            showToast(result.message || 'Product deleted.');
            loadProducts();
        } catch (e) {
            showToast(e.message || 'Delete failed.', 'error');
        }
    }

    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => { currentPage = 1; loadProducts(); }, 350);
    });
    statusFilter?.addEventListener('change', () => { currentPage = 1; loadProducts(); });

    loadProducts();
})();
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
            <tbody id="master-table-body"><tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading products...</td></tr></tbody>
        </table>
    </div>
    <div id="products-pagination"></div>
</div>
<div id="master-toast" class="hidden fixed top-4 right-4 z-[100]"></div>
@endsection
