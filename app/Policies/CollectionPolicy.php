<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;

class CollectionPolicy
{
    private function getUserRole(User $user, $workspaceId)
    {
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
        if (!$workspace) {
            return null;
        }
        return $workspace->pivot->role_id ? \App\Models\Role::find($workspace->pivot->role_id) : null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Collection $collection): bool
    {
        return $user->workspaces->contains($collection->workspace_id);
    }

    public function create(User $user): bool
    {
        $workspace = app('workspace');
        $role = $this->getUserRole($user, $workspace->id);
        
        if (!$role) {
            return false;
        }

        // Only Owner, Admin, and Editor can create collections
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function update(User $user, Collection $collection): bool
    {
        $role = $this->getUserRole($user, $collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Only Owner, Admin, and Editor can update collections
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function delete(User $user, Collection $collection): bool
    {
        $role = $this->getUserRole($user, $collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Only Owner and Admin can delete collections
        return in_array($role->slug, ['owner', 'admin']);
    }
}
