<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Collection Permissions
            [
                'name' => 'Create Collections',
                'slug' => 'collections.create',
                'module' => 'collections',
                'description' => 'Create new collections in workspace'
            ],
            [
                'name' => 'View Collections',
                'slug' => 'collections.view',
                'module' => 'collections',
                'description' => 'View collections and their schemas'
            ],
            [
                'name' => 'Update Collections',
                'slug' => 'collections.update',
                'module' => 'collections',
                'description' => 'Edit existing collections and schemas'
            ],
            [
                'name' => 'Delete Collections',
                'slug' => 'collections.delete',
                'module' => 'collections',
                'description' => 'Delete collections from workspace'
            ],

            // Record Permissions
            [
                'name' => 'Create Records',
                'slug' => 'records.create',
                'module' => 'records',
                'description' => 'Create new records in collections'
            ],
            [
                'name' => 'View Records',
                'slug' => 'records.view',
                'module' => 'records',
                'description' => 'View records in collections'
            ],
            [
                'name' => 'Update Records',
                'slug' => 'records.update',
                'module' => 'records',
                'description' => 'Edit existing records'
            ],
            [
                'name' => 'Delete Records',
                'slug' => 'records.delete',
                'module' => 'records',
                'description' => 'Delete records from collections'
            ],

            // Activity Permissions
            [
                'name' => 'Create Activities',
                'slug' => 'activities.create',
                'module' => 'activities',
                'description' => 'Add activities to records'
            ],
            [
                'name' => 'Sign Off Activities',
                'slug' => 'activities.signoff',
                'module' => 'activities',
                'description' => 'Sign off activities on records'
            ],
            [
                'name' => 'Delete Activities',
                'slug' => 'activities.delete',
                'module' => 'activities',
                'description' => 'Delete activities from records'
            ],

            // Member Permissions
            [
                'name' => 'View Members',
                'slug' => 'members.view',
                'module' => 'members',
                'description' => 'View workspace members list'
            ],
            [
                'name' => 'Invite Members',
                'slug' => 'members.invite',
                'module' => 'members',
                'description' => 'Send invitations to new members'
            ],
            [
                'name' => 'Manage Members',
                'slug' => 'members.manage',
                'module' => 'members',
                'description' => 'Change member roles and remove members'
            ],

            // Workspace Permissions
            [
                'name' => 'Manage Workspace',
                'slug' => 'workspace.manage',
                'module' => 'workspace',
                'description' => 'Update workspace settings'
            ],
            [
                'name' => 'Delete Workspace',
                'slug' => 'workspace.delete',
                'module' => 'workspace',
                'description' => 'Delete the entire workspace'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
