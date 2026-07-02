<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.admin-users.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::with('role')->where('is_admin', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->get()->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'role_id' => $u->role_id,
                'role_name' => $u->role?->name,
                'status' => $u->status ? 'active' : 'inactive',
                'created_at' => $u->created_at?->format('M d, Y'),
            ]),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with('role')->where('is_admin', true)->findOrFail($id);

        return response()->json(['success' => true, 'data' => $user]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'] ?? null,
            'is_admin' => true,
            'status' => $data['status'] === 'active',
            'email_verified_at' => now(),
        ]);

        ActivityLogger::log('created', 'admin-users', $user, 'Admin user created');

        return response()->json(['success' => true, 'message' => 'Admin user created successfully.', 'data' => $user]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::where('is_admin', true)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role_id' => $data['role_id'] ?? null,
            'status' => $data['status'] === 'active',
        ];

        if (! empty($data['password'])) {
            $update['password'] = Hash::make($data['password']);
        }

        $user->update($update);
        ActivityLogger::log('updated', 'admin-users', $user, 'Admin user updated');

        return response()->json(['success' => true, 'message' => 'Admin user updated successfully.', 'data' => $user->fresh()->load('role')]);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::where('is_admin', true)->findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();
        ActivityLogger::log('deleted', 'admin-users', null, "Deleted admin user #{$id}");

        return response()->json(['success' => true, 'message' => 'Admin user deleted successfully.']);
    }

    public function rolesList(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => Role::where('status', true)->orderBy('name')->get(['id', 'name'])]);
    }
}
