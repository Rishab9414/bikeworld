/**
 * BikeWorld Admin — AJAX Master CRUD Manager
 */
class MasterCrud {
    constructor(config) {
        this.baseUrl = config.baseUrl;
        this.module = config.module;
        this.tableBody = document.getElementById('master-table-body');
        this.modal = document.getElementById('master-modal');
        this.form = document.getElementById('master-form');
        this.modalTitle = document.getElementById('master-modal-title');
        this.searchInput = document.getElementById('master-search');
        this.statusFilter = document.getElementById('master-status-filter');
        this.recordId = null;
        this.init();
    }

    init() {
        document.getElementById('btn-add')?.addEventListener('click', () => this.openModal());
        document.getElementById('btn-close-modal')?.addEventListener('click', () => this.closeModal());
        document.getElementById('btn-cancel')?.addEventListener('click', () => this.closeModal());
        this.form?.addEventListener('submit', (e) => this.handleSubmit(e));
        this.bindImageFileInputs();
        this.searchInput?.addEventListener('input', this.debounce(() => this.loadData(), 400));
        this.statusFilter?.addEventListener('change', () => this.loadData());
        this.modal?.addEventListener('click', (e) => { if (e.target === this.modal) this.closeModal(); });
        this.loadData();
    }

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    async request(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN': this.csrf(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
        if (!(options.body instanceof FormData)) {
            defaults.headers['Content-Type'] = 'application/json';
        }
        const response = await fetch(url, { ...defaults, ...options });
        const data = await response.json();
        if (!response.ok) {
            if (data.errors) {
                this.showErrors(data.errors);
                throw new Error('Validation failed');
            }
            throw new Error(data.message || 'Request failed');
        }
        return data;
    }

    showErrors(errors) {
        this.form.querySelectorAll('.field-error').forEach(el => el.remove());
        Object.entries(errors).forEach(([field, messages]) => {
            const input = this.form.querySelector(`[name="${field}"], [name="${field}[]"]`);
            if (input) {
                const err = document.createElement('p');
                err.className = 'field-error text-red-500 text-xs mt-1';
                err.textContent = messages[0];
                input.closest('div')?.appendChild(err);
            }
        });
    }

    showToast(message, type = 'success') {
        const toast = document.getElementById('master-toast');
        if (!toast) return;
        toast.textContent = message;
        toast.className = `fixed top-4 right-4 z-[100] px-5 py-3 rounded-xl shadow-lg text-sm font-medium transition-all ${type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'}`;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    async loadData() {
        const params = new URLSearchParams();
        if (this.searchInput?.value) params.set('search', this.searchInput.value);
        if (this.statusFilter?.value) params.set('status', this.statusFilter.value);

        this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-12 text-center text-slate-400"><svg class="animate-spin h-6 w-6 mx-auto mb-2 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Loading...</td></tr>`;

        try {
            const result = await this.request(`${this.baseUrl}/data?${params}`);
            this.renderTable(result.data);
        } catch (e) {
            this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-8 text-center text-red-500">Failed to load data.</td></tr>`;
        }
    }

    renderTable(rows) {
        if (!rows.length) {
            this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-12 text-center text-slate-400">No records found.</td></tr>`;
            return;
        }
        this.tableBody.innerHTML = rows.map(row => this.renderRow(row)).join('');
        this.bindRowActions();
    }

    renderRow(row) {
        return window.renderMasterRow ? window.renderMasterRow(row) : '';
    }

    bindRowActions() {
        this.tableBody.querySelectorAll('[data-edit]').forEach(btn => {
            btn.addEventListener('click', () => this.editRecord(btn.dataset.edit));
        });
        this.tableBody.querySelectorAll('[data-delete]').forEach(btn => {
            btn.addEventListener('click', () => this.deleteRecord(btn.dataset.delete));
        });
        this.tableBody.querySelectorAll('[data-toggle]').forEach(btn => {
            btn.addEventListener('click', () => this.toggleStatus(btn.dataset.toggle));
        });
    }

    openModal(title = 'Add Record') {
        this.recordId = null;
        this.form.reset();
        this.modalTitle.textContent = title;
        this.form.querySelectorAll('.field-error').forEach(el => el.remove());
        this.resetImagePreviews();
        this.modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        window.onMasterModalOpen?.();
    }

    closeModal() {
        this.modal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    async editRecord(id) {
        try {
            const result = await this.request(`${this.baseUrl}/${id}`);
            this.recordId = id;
            this.modalTitle.textContent = 'Edit Record';
            this.populateForm(result.data);
            this.modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            window.onMasterModalOpen?.(result.data);
        } catch (e) {
            this.showToast('Failed to load record.', 'error');
        }
    }

    populateForm(data) {
        Object.entries(data).forEach(([key, value]) => {
            const field = this.form.querySelector(`[name="${key}"]`);
            if (!field) return;
            if (field.type === 'file') return;
            if (field.type === 'checkbox') {
                field.checked = !!value;
            } else if (field.tagName === 'SELECT') {
                field.value = value ?? '';
            } else {
                field.value = value ?? '';
            }
        });
        this.updateImagePreviews(data);
        window.onMasterFormPopulate?.(data);
    }

    resetImagePreviews() {
        this.form.querySelectorAll('[data-image-preview]').forEach((el) => {
            el.classList.add('hidden');
            el.removeAttribute('src');
        });
    }

    updateImagePreviews(data = {}) {
        this.form.querySelectorAll('[data-image-preview]').forEach((el) => {
            const url = data[el.dataset.imagePreview] || '';
            if (url) {
                el.src = url;
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
                el.removeAttribute('src');
            }
        });
    }

    bindImageFileInputs() {
        this.form.querySelectorAll('input[type="file"][data-preview-target]').forEach((input) => {
            input.addEventListener('change', () => {
                const preview = this.form.querySelector(`[data-image-preview="${input.dataset.previewTarget}"]`);
                if (!preview) return;
                const file = input.files?.[0];
                if (!file) {
                    preview.classList.add('hidden');
                    preview.removeAttribute('src');
                    return;
                }
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
            });
        });
    }

    formUsesFiles() {
        return [...this.form.elements].some((el) => el.type === 'file' && el.files?.length > 0);
    }

    buildFormBody() {
        if (!this.formUsesFiles()) {
            return { body: JSON.stringify(this.getFormData()), useMethodOverride: false };
        }

        const fd = new FormData(this.form);
        this.form.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
            fd.set(cb.name, cb.checked ? '1' : '0');
        });
        if (this.recordId) {
            fd.append('_method', 'PUT');
        }

        return { body: fd, useMethodOverride: !!this.recordId };
    }

    getFormData() {
        const fd = new FormData(this.form);
        const data = {};
        fd.forEach((value, key) => {
            if (key.endsWith('[]')) {
                const k = key.slice(0, -2);
                data[k] = data[k] || [];
                data[k].push(value);
            } else {
                data[key] = value;
            }
        });
        this.form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            data[cb.name] = cb.checked;
        });
        return data;
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.form.querySelectorAll('.field-error').forEach(el => el.remove());
        const { body, useMethodOverride } = this.buildFormBody();
        const btn = this.form.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        try {
            const url = this.recordId ? `${this.baseUrl}/${this.recordId}` : this.baseUrl;
            const method = useMethodOverride ? 'POST' : (this.recordId ? 'PUT' : 'POST');
            const result = await this.request(url, { method, body });
            this.showToast(result.message);
            this.closeModal();
            this.loadData();
        } catch (e) {
            if (e.message !== 'Validation failed') this.showToast(e.message, 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    }

    async deleteRecord(id) {
        if (!confirm('Are you sure you want to delete this record?')) return;
        try {
            const result = await this.request(`${this.baseUrl}/${id}`, { method: 'DELETE' });
            this.showToast(result.message);
            this.loadData();
        } catch (e) {
            this.showToast(e.message, 'error');
        }
    }

    async toggleStatus(id) {
        try {
            const result = await this.request(`${this.baseUrl}/${id}/toggle-status`, { method: 'PATCH' });
            this.showToast(result.message);
            this.loadData();
        } catch (e) {
            this.showToast(e.message, 'error');
        }
    }
}

window.MasterCrud = MasterCrud;

class ReadOnlyTable {
    constructor(config) {
        this.baseUrl = config.baseUrl;
        this.tableBody = document.getElementById('master-table-body');
        this.searchInput = document.getElementById('master-search');
        this.init();
    }

    init() {
        this.searchInput?.addEventListener('input', this.debounce(() => this.loadData(), 400));
        this.loadData();
    }

    debounce(fn, ms) {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
    }

    async loadData() {
        const params = new URLSearchParams();
        if (this.searchInput?.value) params.set('search', this.searchInput.value);
        this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-12 text-center text-slate-400">Loading...</td></tr>`;
        try {
            const response = await fetch(`${this.baseUrl}/data?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            this.renderTable(result.data);
        } catch (e) {
            this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-8 text-center text-red-500">Failed to load.</td></tr>`;
        }
    }

    renderTable(rows) {
        if (!rows.length) {
            this.tableBody.innerHTML = `<tr><td colspan="20" class="px-6 py-12 text-center text-slate-400">No records found.</td></tr>`;
            return;
        }
        this.tableBody.innerHTML = rows.map(row => window.renderMasterRow(row)).join('');
    }
}

window.ReadOnlyTable = ReadOnlyTable;
