<?php

namespace App\Traits;

use App\Models\Role;

trait HasPermissions
{
    /**
     * Check if user has a specific permission in the current workspace
     */
    public function hasPermission(string $permissionSlug): bool
    {
        $workspace = app('workspace');
        
        if (!$workspace) {
            return false;
        }

        $userWorkspace = $this->workspaces()->where('workspaces.id', $workspace->id)->first();
        
        if (!$userWorkspace) {
            return false;
        }

        $role = Role::find($userWorkspace->pivot->role_id);
        
        if (!$role) {
            return false;
        }

        return $role->hasPermission($permissionSlug);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get user's role in current workspace
     */
    public function getWorkspaceRole()
    {
        $workspace = app('workspace');
        
        if (!$workspace) {
            return null;
        }

        $userWorkspace = $this->workspaces()->where('workspaces.id', $workspace->id)->first();
        
        if (!$userWorkspace) {
            return null;
        }

        return Role::find($userWorkspace->pivot->role_id);
    }
}
