<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RbacSyncSeeder extends Seeder
{
    /**
     * Idempotently ensures all admin permissions exist and keeps the
     * Super Admin role synced with every permission. Safe to re-run.
     */
    public function run(): void
    {
        $groups = [
            'dashboard' => ['view-dashboard'],
            'masters' => ['manage-categories', 'manage-brands', 'manage-manufacturers', 'manage-suppliers', 'manage-taxes', 'manage-units', 'manage-sizes', 'manage-colors', 'manage-materials', 'manage-vehicle-brands', 'manage-bike-models'],
            'users' => ['manage-admin-users', 'manage-roles', 'manage-permissions', 'view-login-history', 'view-activity-logs'],
            'products' => ['manage-products', 'manage-inventory'],
            'orders' => ['manage-orders', 'manage-returns'],
            'customers' => ['manage-customers'],
            'marketing' => ['manage-marketing'],
            'reports' => ['view-reports'],
            'settings' => ['manage-settings'],
        ];

        foreach ($groups as $group => $slugs) {
            foreach ($slugs as $slug) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::title(str_replace('-', ' ', $slug)), 'group' => $group],
                );
            }
        }

        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::pluck('id'));
        }
    }
}
