@extends('admin.layouts.app')
@section('title', 'Login History')
@section('page-title', 'Login History')
@section('page-subtitle', 'View admin user login activity')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-slate-900">${row.user_name || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.user_email || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600 font-mono">${row.ip_address || '—'}</td>
    <td class="px-5 py-3.5">${row.status === 'success'
        ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">Success</span>'
        : row.status === 'failed'
            ? '<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Failed</span>'
            : `<span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">${row.status || '—'}</span>`}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.logged_in_at || '—'}</td>
</tr>`;
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.users.login-history.index')"
    module="login-history"
    :read-only="true"
    :show-add-button="false"
    :show-actions="false"
    :columns="[['label'=>'User'],['label'=>'Email'],['label'=>'IP'],['label'=>'Status'],['label'=>'Logged In At']]"
/>
@endsection
