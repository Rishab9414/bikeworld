<?php

namespace App\Http\Controllers\Admin\Masters;

use App\Http\Controllers\Admin\AjaxMasterController;
use App\Models\Tax;

class TaxController extends AjaxMasterController
{
    protected function modelClass(): string { return Tax::class; }
    protected function moduleName(): string { return 'taxes'; }
    protected function searchColumns(): array { return ['name', 'hsn_code']; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'hsn_code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
