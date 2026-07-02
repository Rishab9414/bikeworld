<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;

class BrandController extends AjaxMasterController
{
    protected function modelClass(): string { return Brand::class; }
    protected function moduleName(): string { return 'brands'; }
    protected function searchColumns(): array { return ['name', 'country', 'website']; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug,'.$id],
            'logo' => ['nullable', 'string', 'max:255'],
            'banner' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_keywords' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
