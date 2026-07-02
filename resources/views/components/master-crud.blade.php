@props([
    'baseUrl',
    'columns',
    'module' => '',
    'readOnly' => false,
    'showAddButton' => true,
    'showStatusFilter' => true,
    'showActions' => true,
])

<div id="master-toast" class="hidden fixed top-4 right-4 z-[100]"></div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 border-b border-slate-100">
        <div class="flex flex-1 gap-3">
            <input type="text" id="master-search" placeholder="Search..." class="admin-input max-w-xs text-sm py-2.5">
            @if($showStatusFilter)
            <select id="master-status-filter" class="admin-input max-w-[140px] text-sm py-2.5">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            @endif
        </div>
        @if($showAddButton)
        <button id="btn-add" type="button" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors shadow-lg shadow-indigo-600/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add New
        </button>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50">
                <tr>
                    @foreach($columns as $col)
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $col['label'] }}</th>
                    @endforeach
                    @if($showActions)
                    <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody id="master-table-body" class="divide-y divide-slate-100">
                <tr><td colspan="20" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@if($showAddButton)
<div id="master-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
            <h3 id="master-modal-title" class="text-lg font-bold text-slate-900">Add Record</h3>
            <button type="button" id="btn-close-modal" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="master-form" class="p-6 space-y-4">
            {{ $form ?? '' }}
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" id="btn-cancel" class="px-5 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl">Save</button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script type="module">
(function bootMasterCrud() {
    const baseUrl = @js($baseUrl);
    const module = @js($module);
    const readOnly = @js($readOnly);

    const start = () => {
        if (typeof window.renderMasterRow !== 'function') {
            setTimeout(start, 30);
            return;
        }
        if (readOnly) {
            new ReadOnlyTable({ baseUrl });
        } else {
            new MasterCrud({ baseUrl, module });
        }
    };
    start();
})();
</script>
@endpush
