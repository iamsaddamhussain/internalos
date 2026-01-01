<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Run permission seeder first
        $this->call(PermissionSeeder::class);

        // Create System Roles (workspace_id = null means system-wide)
        $roles = [
            [
                'name' => 'Owner',
                'slug' => 'owner',
                'description' => 'Full access to workspace',
                'permissions' => Permission::all()->pluck('id')->toArray(),
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Manage collections and records',
                'permissions' => Permission::whereIn('slug', [
                    'collections.create', 'collections.view', 'collections.update', 'collections.delete',
                    'records.create', 'records.view', 'records.update', 'records.delete',
                    'members.view', 'members.invite', 'members.manage',
                    'workspace.manage',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Create and edit records',
                'permissions' => Permission::whereIn('slug', [
                    'collections.view', 'collections.create', 'collections.update',
                    'records.create', 'records.view', 'records.update',
                    'members.view',
                ])->pluck('id')->toArray(),
            ],
            [
                'name' => 'Viewer',
                'slug' => 'viewer',
                'description' => 'Read-only access',
                'permissions' => Permission::whereIn('slug', [
                    'collections.view',
                    'records.view',
                    'members.view',
                ])->pluck('id')->toArray(),
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug'], 'workspace_id' => null],
                $roleData
            );

            $role->permissions()->sync($permissions);
        }
    }
}
