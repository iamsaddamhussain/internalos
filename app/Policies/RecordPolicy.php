<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\Record;
use App\Models\User;

class RecordPolicy
{
    private function getUserRole(User $user, $workspaceId)
    {
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
        if (!$workspace) {
            return null;
        }
        return $workspace->pivot->role_id ? \App\Models\Role::find($workspace->pivot->role_id) : null;
    }

    public function view(User $user, Record $record): bool
    {
        $role = $this->getUserRole($user, $record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // All roles can view records (Owner, Admin, Editor, Viewer)
        return in_array($role->slug, ['owner', 'admin', 'editor', 'viewer']);
    }

    public function create(User $user, Collection $collection): bool
    {
        $role = $this->getUserRole($user, $collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Only Owner, Admin, and Editor can create records
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function update(User $user, Record $record): bool
    {
        $role = $this->getUserRole($user, $record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Only Owner, Admin, and Editor can update records
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function delete(User $user, Record $record): bool
    {
        $role = $this->getUserRole($user, $record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Only Owner and Admin can delete records
        return in_array($role->slug, ['owner', 'admin']);
    }
}
