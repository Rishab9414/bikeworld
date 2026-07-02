@extends('admin.layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')
@section('page-subtitle', 'View system activity and audit trail')

@push('scripts')
<script type="module">
window.renderMasterRow = (row) => `<tr class="hover:bg-slate-50">
    <td class="px-5 py-3.5 font-medium text-slate-900">${row.user_name || 'System'}</td>
    <td class="px-5 py-3.5"><span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-700 capitalize">${row.action || '—'}</span></td>
    <td class="px-5 py-3.5 text-sm text-slate-600 capitalize">${row.module || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600 max-w-xs truncate">${row.description || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600 font-mono">${row.ip_address || '—'}</td>
    <td class="px-5 py-3.5 text-sm text-slate-600">${row.created_at || '—'}</td>
</tr>`;
</script>
@endpush

@section('content')
<x-master-crud
    :base-url="route('admin.users.activity-logs.index')"
    module="activity-logs"
    :read-only="true"
    :show-add-button="false"
    :show-actions="false"
    :columns="[['label'=>'User'],['label'=>'Action'],['label'=>'Module'],['label'=>'Description'],['label'=>'IP'],['label'=>'Created At']]"
/>
@endsection
