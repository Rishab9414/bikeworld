<div id="quick-master-modal" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 id="quick-master-title" class="text-lg font-bold text-slate-900">Add</h3>
            <button type="button" class="quick-master-close-btn p-2 text-slate-400 hover:text-slate-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="quick-master-form" class="p-6 space-y-4">
            <div id="quick-master-fields" class="space-y-4"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="quick-master-close-btn px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-semibold bg-indigo-600 text-white rounded-xl hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
