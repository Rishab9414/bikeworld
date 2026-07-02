<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CategoryController extends AjaxMasterController
{
    protected function modelClass(): string { return Category::class; }
    protected function moduleName(): string { return 'categories'; }
    protected function searchColumns(): array { return ['name', 'slug', 'description']; }
    protected function orderColumn(): string { return 'display_order'; }
    protected function orderDirection(): string { return 'asc'; }
    protected function withRelations(): array { return ['parent']; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,'.$id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'string', 'max:255'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function beforeStore(array &$data): void
    {
        $data['slug'] ??= Str::slug($data['name']);
        $data['is_active'] = ($data['status'] ?? 'active') === 'active';
        $data['featured'] = filter_var($data['featured'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['show_in_menu'] = filter_var($data['show_in_menu'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    protected function beforeUpdate(array &$data, Model $record): void
    {
        $this->beforeStore($data);
    }

    protected function transformRecord(Model $record): array
    {
        $data = $record->toArray();
        $data['parent_name'] = $record->parent?->name;

        return $data;
    }
}
