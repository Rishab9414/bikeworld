@extends('admin.layouts.app')
@section('title', 'Permissions')
@section('page-title', 'Permissions')
@section('page-subtitle', 'Manage system permissions')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-slate-900">${row.name}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600 font-mono">${row.slug}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.group || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600 max-w-xs truncate">${row.description || '—'}</td>
    <td class="px-5 py-3.5 text-right"><div class="flex justify-end gap-1">
        <button data-edit="${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
        <button data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div></td>
</tr>`;
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.users.permissions.index')"
    module="permissions"
    :show-status-filter="false"
    :columns="[['label'=>'Name'],['label'=>'Slug'],['label'=>'Group'],['label'=>'Description']]"
>
    <x-slot:form>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Permission Name *</label><input name="name" required class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Slug *</label><input name="slug" required class="admin-input text-sm" placeholder="e.g. users.create"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Group *</label><input name="group" required class="admin-input text-sm" placeholder="e.g. Users"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Description</label><textarea name="description" rows="2" class="admin-input text-sm"></textarea></div>
        </div>
    </x-slot:form>
</x-master-crud>
@endsection
