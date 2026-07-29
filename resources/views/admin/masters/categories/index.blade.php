@extends('admin.layouts.app')
@section('title', 'Categories')
@section('page-title', 'Category Master')
@section('page-subtitle', 'Manage product categories and homepage category photos')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5">
        ${row.image_url
            ? `<img src="${row.image_url}" alt="" class="w-14 h-14 rounded-lg object-cover border border-slate-200">`
            : `<div class="w-14 h-14 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-[10px]">No img</div>`}
    </td>
    <td class="px-5 py-3.5"><div class="font-medium text-slate-900">${row.name}</div><div class="text-xs text-slate-400">${row.slug || ''}</div></td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.parent_name || '—'}</td>
    <td class="px-5 py-3.5 text-sm">${row.display_order || 0}</td>
    <td class="px-5 py-3.5">${row.featured ? '<span class="text-amber-600 text-xs font-semibold">★ Featured</span>' : '—'}</td>
    <td class="px-5 py-3.5">${row.status === 'active'
        ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>'
        : '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>'}</td>
    <td class="px-5 py-3.5 text-right"><div class="flex justify-end gap-1">
        <button data-edit="${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
        <button data-toggle="${row.id}" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg" title="Toggle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></button>
        <button data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div></td>
</tr>`;

window.onMasterModalOpen = async () => {
    const res = await fetch(@js(route('admin.masters.categories.data')), { headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    const sel = document.getElementById('parent_id');
    sel.innerHTML = '<option value="">None (Root)</option>' + json.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
};
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.masters.categories.index')"
    module="categories"
    :columns="[['label'=>'Photo'],['label'=>'Name'],['label'=>'Parent'],['label'=>'Order'],['label'=>'Featured'],['label'=>'Status']]"
>
    <x-slot:form>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Category Name *</label><input name="name" required class="admin-input text-sm"></div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1">Category Photo (Shop by Category)</label>
                <img data-image-preview="image_url" class="hidden w-full max-w-xs h-40 object-cover rounded-xl border border-slate-200 mb-2" alt="Preview">
                <input type="file" name="image_file" accept="image/jpeg,image/jpg,image/png,image/webp" data-preview-target="image_url" class="admin-input text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-medium">
                <p class="text-xs text-slate-400 mt-1">Shown on homepage Shop by Category cards (JPEG, PNG, WebP — max 4MB). Recommended portrait ~800×1000.</p>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Parent Category</label><select name="parent_id" id="parent_id" class="admin-input text-sm"><option value="">None (Root)</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Display Order</label><input name="display_order" type="number" min="0" value="0" class="admin-input text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea name="description" rows="2" class="admin-input text-sm"></textarea></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">SEO Title</label><input name="seo_title" class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select name="status" class="admin-input text-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">SEO Keywords</label><input name="seo_keywords" class="admin-input text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Meta Description</label><textarea name="meta_description" rows="2" class="admin-input text-sm"></textarea></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="featured" id="featured" class="rounded text-indigo-600"><label for="featured" class="text-sm text-slate-700">Featured Category</label></div>
            <div class="flex items-center gap-2"><input type="checkbox" name="show_in_menu" id="show_in_menu" checked class="rounded text-indigo-600"><label for="show_in_menu" class="text-sm text-slate-700">Show in Menu</label></div>
        </div>
    </x-slot:form>
</x-master-crud>
@endsection
