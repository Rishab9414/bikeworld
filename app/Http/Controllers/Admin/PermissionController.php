<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use Illuminate\View\View;

class PermissionController extends AjaxMasterController
{
    protected function modelClass(): string { return Permission::class; }
    protected function moduleName(): string { return 'permissions'; }
    protected function searchColumns(): array { return ['name', 'slug', 'group']; }
    protected function orderColumn(): string { return 'group'; }
    protected function orderDirection(): string { return 'asc'; }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:permissions,slug,'.$id],
            'group' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function index(): View
    {
        return view('admin.users.permissions.index');
    }

    protected function viewName(): string
    {
        return 'admin.users.permissions.index';
    }
}
