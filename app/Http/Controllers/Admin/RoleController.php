<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends AjaxMasterController
{
    protected function modelClass(): string { return Role::class; }
    protected function moduleName(): string { return 'roles'; }
    protected function withRelations(): array { return ['permissions']; }
    protected function orderColumn(): string { return 'name'; }
    protected function orderDirection(): string { return 'asc'; }

    public function index(): View
    {
        return view('admin.users.roles.index');
    }

    protected function viewName(): string
    {
        return 'admin.users.roles.index';
    }

    protected function validationRules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug,'.$id],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }

    protected function beforeStore(array &$data): void
    {
        $data['slug'] ??= Str::slug($data['name']);
        $data['status'] = filter_var($data['status'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }

    protected function beforeUpdate(array &$data, Model $record): void
    {
        $this->beforeStore($data);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->validationRules());
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $this->beforeStore($data);

        $role = Role::create($data);
        $role->permissions()->sync($permissions);

        ActivityLogger::log('created', 'roles', $role, 'Role created');

        return response()->json(['success' => true, 'message' => 'Role created successfully.', 'data' => $this->transformRecord($role->load('permissions'))]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $data = $request->validate($this->validationRules($id));
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);
        $this->beforeUpdate($data, $role);

        $role->update($data);
        $role->permissions()->sync($permissions);

        ActivityLogger::log('updated', 'roles', $role, 'Role updated');

        return response()->json(['success' => true, 'message' => 'Role updated successfully.', 'data' => $this->transformRecord($role->fresh()->load('permissions'))]);
    }

    protected function transformRecord(Model $record): array
    {
        $data = $record->toArray();
        $data['permission_ids'] = $record->permissions->pluck('id');

        return $data;
    }

    public function permissionsList(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Permission::orderBy('group')->orderBy('name')->get()->groupBy('group'),
        ]);
    }

    public function toggleStatus(int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $role->status = ! $role->status;
        $role->save();

        ActivityLogger::log('status_changed', 'roles', $role, 'Role status changed');

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'data' => $this->transformRecord($role),
        ]);
    }
}
