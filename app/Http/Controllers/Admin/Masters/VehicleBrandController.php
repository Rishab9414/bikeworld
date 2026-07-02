<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\VehicleBrand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VehicleBrandController extends AjaxMasterController
{
    protected function modelClass(): string { return VehicleBrand::class; }
    protected function moduleName(): string { return 'vehicle-brands'; }
    protected function searchColumns(): array { return ['name', 'slug']; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:vehicle_brands,slug,'.$id],
            'logo' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function beforeStore(array &$data): void
    {
        $data['slug'] ??= Str::slug($data['name']);
    }

    protected function beforeUpdate(array &$data, Model $record): void
    {
        $this->beforeStore($data);
    }
}
