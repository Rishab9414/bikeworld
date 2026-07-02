<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\Size;

class SizeController extends AjaxMasterController
{
    protected function modelClass(): string { return Size::class; }
    protected function moduleName(): string { return 'sizes'; }
    protected function orderColumn(): string { return 'display_order'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
