<?php

namespace App\Policies;

use App\Models\Automation;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Access\Response;

class AutomationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $workspace->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Automation $automation, Workspace $workspace): bool
    {
        return $automation->workspace_id === $workspace->id 
            && $workspace->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Workspace $workspace): bool
    {
        // Only owners and admins can create automations
        $userWorkspace = $workspace->users()
            ->where('users.id', $user->id)
            ->first();

        if (!$userWorkspace) {
            return false;
        }

        $role = $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id)
            : null;

        return $role && in_array($role->slug, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Automation $automation, Workspace $workspace): bool
    {
        if ($automation->workspace_id !== $workspace->id) {
            return false;
        }

        // Only owners and admins can update automations
        $userWorkspace = $workspace->users()
            ->where('users.id', $user->id)
            ->first();

        if (!$userWorkspace) {
            return false;
        }

        $role = $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id)
            : null;

        return $role && in_array($role->slug, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Automation $automation, Workspace $workspace): bool
    {
        if ($automation->workspace_id !== $workspace->id) {
            return false;
        }

        // Only owners and admins can delete automations
        $userWorkspace = $workspace->users()
            ->where('users.id', $user->id)
            ->first();

        if (!$userWorkspace) {
            return false;
        }

        $role = $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id)
            : null;

        return $role && in_array($role->slug, ['owner', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Automation $automation): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Automation $automation): bool
    {
        return false;
    }
}
