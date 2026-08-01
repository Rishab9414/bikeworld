/**
 * Product form helpers — quick master add + dynamic rows
 */
window.productForm = {
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    },

    async quickAdd(storeUrl, fields, selectEl) {
        const body = {};
        fields.forEach(f => {
            const el = document.getElementById(f.id);
            if (el) body[f.name] = el.value;
        });
        if (!body.status) body.status = 'active';

        const wrap = selectEl?.closest('[data-master-wrap]');
        if (wrap?.dataset.masterLabel?.includes('Sub Category')) {
            const catId = document.getElementById('category_id')?.value;
            if (!catId) throw new Error('Please select a parent category first.');
            body.parent_id = catId;
        }

        const res = await fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrf(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body),
        });
        const json = await res.json();
        if (!res.ok) {
            let msg = json.message || 'Failed to add';
            if (json.errors) {
                msg = Object.values(json.errors).flat().join('\n');
            }
            throw new Error(msg);
        }
        const item = json.data;
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.name;
        opt.selected = true;
        selectEl.appendChild(opt);
        selectEl.value = item.id;

        if (body.parent_id && window.productFormMasters?.sub_categories) {
            window.productFormMasters.sub_categories.push({
                id: item.id,
                name: item.name,
                parent_id: parseInt(body.parent_id, 10),
            });
        }

        if (storeUrl.includes('/colors') && window.productFormMasters?.colors) {
            window.productFormMasters.colors.push({ id: item.id, name: item.name });
        }
        if (storeUrl.includes('/sizes') && window.productFormMasters?.sizes) {
            window.productFormMasters.sizes.push({ id: item.id, name: item.name });
        }

        return json;
    },

    filterSubCategories(parentId, subSelect, allSubs) {
        subSelect.innerHTML = '<option value="">Select Sub Category</option>';
        allSubs.filter(s => String(s.parent_id) === String(parentId)).forEach(s => {
            const o = document.createElement('option');
            o.value = s.id;
            o.textContent = s.name;
            subSelect.appendChild(o);
        });
    },

    addVariantRow(container, masters, data = {}) {
        const idx = container.querySelectorAll('.variant-row').length;
        const imagePreview = data.image_url
            ? `<img src="${data.image_url}" alt="Variant" class="variant-image-preview mt-2 w-16 h-16 object-cover rounded-lg border border-slate-200">`
            : '';
        const row = document.createElement('div');
        row.className = 'variant-row space-y-3 p-4 bg-slate-50 rounded-xl border border-slate-200';
        row.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
            <div><label class="text-xs text-slate-500">SKU</label><input name="variants[${idx}][sku]" value="${data.sku || ''}" class="admin-input text-sm py-2"></div>
            <div data-master-wrap data-store-url="/admin/masters/colors" data-master-label="Add Color" data-fields='[{"name":"name","label":"Color Name","required":true}]'>
                <label class="text-xs text-slate-500">Color</label>
                <div class="flex gap-1"><select name="variants[${idx}][color_id]" class="admin-input text-sm py-2 flex-1">${this.optionsHtml(masters.colors, data.color_id)}</select>
                <button type="button" data-quick-add class="shrink-0 w-8 h-8 flex items-center justify-center bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-lg font-bold">+</button></div>
            </div>
            <div data-master-wrap data-store-url="/admin/masters/sizes" data-master-label="Add Size" data-fields='[{"name":"name","label":"Size Name","required":true}]'>
                <label class="text-xs text-slate-500">Size</label>
                <div class="flex gap-1"><select name="variants[${idx}][size_id]" class="admin-input text-sm py-2 flex-1">${this.optionsHtml(masters.sizes, data.size_id)}</select>
                <button type="button" data-quick-add class="shrink-0 w-8 h-8 flex items-center justify-center bg-indigo-100 text-indigo-700 hover:bg-indigo-200 rounded-lg font-bold">+</button></div>
            </div>
            <div><label class="text-xs text-slate-500">Price</label><input name="variants[${idx}][price]" type="number" step="0.01" value="${data.price || ''}" class="admin-input text-sm py-2"></div>
            <div><label class="text-xs text-slate-500">Stock</label><input name="variants[${idx}][stock]" type="number" value="${data.stock || 0}" class="admin-input text-sm py-2"></div>
            <div class="flex items-end"><button type="button" class="remove-variant text-red-600 text-sm font-medium py-2">Remove</button></div>
            </div>
            <div>
                <label class="text-xs text-slate-500">Variant Image</label>
                <input type="hidden" name="variants[${idx}][existing_image]" value="${data.image || ''}">
                <input type="file" name="variants[${idx}][image]" accept="image/jpeg,image/png,image/webp" class="admin-input text-sm py-2">
                ${imagePreview}
            </div>
        `;
        row.querySelector('.remove-variant').addEventListener('click', () => row.remove());
        container.appendChild(row);
        this.initQuickAddButtons(row);
    },

    addFeatureRow(container, value = '') {
        const row = document.createElement('div');
        row.className = 'flex gap-2 feature-row';
        row.innerHTML = `<input name="features[]" value="${value}" class="admin-input text-sm flex-1" placeholder="e.g. ISI Certified"><button type="button" class="remove-feature text-red-500 px-2">×</button>`;
        row.querySelector('.remove-feature').addEventListener('click', () => row.remove());
        container.appendChild(row);
    },

    optionsHtml(items, selected = '') {
        let html = '<option value="">—</option>';
        (items || []).forEach(i => {
            html += `<option value="${i.id}" ${String(i.id) === String(selected) ? 'selected' : ''}>${i.name}</option>`;
        });
        return html;
    },

    initQuickAddButtons(scope = document) {
        scope.querySelectorAll('[data-quick-add]').forEach(btn => {
            if (btn.dataset.bound) return;
            btn.dataset.bound = '1';
            btn.addEventListener('click', () => {
                const wrap = btn.closest('[data-master-wrap]');
                const modal = document.getElementById('quick-master-modal');
                const storeUrl = wrap.dataset.storeUrl;
                const fields = JSON.parse(wrap.dataset.fields || '[]');
                const selectEl = wrap.querySelector('select');
                const title = wrap.dataset.masterLabel || 'Add';

                document.getElementById('quick-master-title').textContent = title;
                const fieldsContainer = document.getElementById('quick-master-fields');
                fieldsContainer.innerHTML = fields.filter(f => f.label).map(f => `
                    <div><label class="block text-sm font-medium text-slate-700 mb-1">${f.label}${f.required ? ' *' : ''}</label>
                    <input id="qm_${f.name}" name="${f.name}" ${f.required ? 'required' : ''} class="admin-input text-sm" placeholder="${f.placeholder || ''}"></div>
                `).join('');

                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                const form = document.getElementById('quick-master-form');
                form.onsubmit = async (e) => {
                    e.preventDefault();
                    try {
                        await this.quickAdd(storeUrl, fields.map(f => ({ id: 'qm_' + f.name, name: f.name })), selectEl);
                        modal.classList.add('hidden');
                        document.body.style.overflow = '';
                    } catch (err) {
                        alert(err.message);
                    }
                };
            });
        });
    },
};

document.addEventListener('DOMContentLoaded', () => {
    window.productForm.initQuickAddButtons();

    document.querySelectorAll('.quick-master-close-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('quick-master-modal').classList.add('hidden');
            document.body.style.overflow = '';
        });
    });
});
