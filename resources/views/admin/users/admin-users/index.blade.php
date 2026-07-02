@extends('admin.layouts.app')
@section('title', 'Admin Users')
@section('page-title', 'Admin Users')
@section('page-subtitle', 'Manage admin panel users')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-slate-900">${row.name}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.email}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.phone || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.role_name || '—'}</td>
    <td class="px-5 py-3.5">${row.status === 'active'
        ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Active</span>'
        : '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Inactive</span>'}</td>
    <td class="px-5 py-3.5 text-right"><div class="flex justify-end gap-1">
        <button data-edit="${row.id}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
        <button data-delete="${row.id}" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
    </div></td>
</tr>`;

window.onMasterModalOpen = async (data) => {
    const res = await fetch(@js(route('admin.users.admin-users.roles-list')), { headers: { 'Accept': 'application/json' } });
    const json = await res.json();
    const sel = document.getElementById('role_id');
    sel.innerHTML = '<option value="">Select Role</option>' + json.data.map(r => `<option value="${r.id}">${r.name}</option>`).join('');
    if (data?.role_id) sel.value = data.role_id;
};

window.onMasterFormPopulate = (data) => {
    const statusSel = document.querySelector('[name="status"]');
    if (statusSel) statusSel.value = (data.status === true || data.status === 'active') ? 'active' : 'inactive';
    const pwd = document.querySelector('[name="password"]');
    if (pwd) pwd.value = '';
    const roleSel = document.getElementById('role_id');
    if (roleSel && data.role_id) roleSel.value = data.role_id;
};
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.users.admin-users.index')"
    module="admin-users"
    :columns="[['label'=>'Name'],['label'=>'Email'],['label'=>'Phone'],['label'=>'Role'],['label'=>'Status']]"
>
    <x-slot:form>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label><input name="name" required class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Email *</label><input name="email" type="email" required class="admin-input text-sm"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Phone</label><input name="phone" class="admin-input text-sm"></div>
            <div class="sm:col-span-2"><label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-slate-400 font-normal">(leave blank when editing to keep current)</span></label><input name="password" type="password" class="admin-input text-sm" autocomplete="new-password"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Role</label><select name="role_id" id="role_id" class="admin-input text-sm"><option value="">Select Role</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1">Status</label><select name="status" class="admin-input text-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        </div>
    </x-slot:form>
</x-master-crud>
@endsection
