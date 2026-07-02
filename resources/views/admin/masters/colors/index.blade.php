@extends('admin.layouts.app')
@section('title', 'Colors')
@section('page-title', 'Color Master')
@section('page-subtitle', 'Manage product colors')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-slate-900">${row.name}</td>
    <td class="px-5 py-3.5">${row.hex_code ? `<span class="inline-flex items-center gap-2 text-sm text-slate-600"><span class="w-6 h-6 rounded-md border border-slate-200 shadow-sm" style="background-color:${row.hex_code}"></span>${row.hex_code}</span>` : '—'}</td>
    <td class="px-5 py-3.5">${row.status === 'active'
        ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>'
        : '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>'}</td>
    <td class="px-5 py-3.5 text-right"><div class="flex justify-end gap-1">
        <button data-edit="${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
        <button data-toggle="${row.id}" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg" title="Toggle"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg></button>
        <button data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div></td>
</tr>`;
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.masters.colors.index')"
    module="colors"
    :columns="[['label'=>'Name'],['label'=>'Hex Code'],['label'=>'Status']]"
>
    <x-slot:form>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Color Name *</label><input name="name" required class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Hex Code</label><input name="hex_code" type="text" class="admin-input text-sm" placeholder="#FF5733" pattern="#[0-9A-Fa-f]{6}"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select name="status" class="admin-input text-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-slot:form>
</x-master-crud>
@endsection
