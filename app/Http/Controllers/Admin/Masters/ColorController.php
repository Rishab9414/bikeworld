<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\Color;

class ColorController extends AjaxMasterController
{
    protected function modelClass(): string { return Color::class; }
    protected function moduleName(): string { return 'colors'; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'hex_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
