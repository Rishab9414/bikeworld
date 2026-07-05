<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\BikeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BikeModelController extends AjaxMasterController
{
    protected function modelClass(): string { return BikeModel::class; }
    protected function moduleName(): string { return 'bike-models'; }
    protected function searchColumns(): array { return ['name', 'year']; }
    protected function withRelations(): array { return ['vehicleBrand']; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function fileFields(): array
    {
        return ['image_file' => 'image'];
    }

    protected function uploadDirectory(): string
    {
        return 'bike-models';
    }

    protected function validationRules(?int $id = null): array
    {
        return [
            'vehicle_brand_id' => ['required', 'exists:vehicle_brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:500'],
            'image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'year' => ['nullable', 'string', 'max:10'],
            'engine_cc' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_in_shop' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    protected function beforeStore(array &$data): void
    {
        $data['slug'] ??= Str::slug($data['name']);
        $data['show_in_shop'] = filter_var($data['show_in_shop'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
    }

    protected function beforeUpdate(array &$data, Model $record): void
    {
        $this->beforeStore($data);
    }

    protected function transformRecord(Model $record): array
    {
        $data = $record->toArray();
        $data['vehicle_brand_name'] = $record->vehicleBrand?->name;
        $data['image_url'] = $record->imageUrl();

        return $data;
    }
}
