<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Record;
use App\Models\User;

class ActivityPolicy
{
    private function getUserRole(User $user, $workspaceId)
    {
        $workspace = $user->workspaces()->where('workspaces.id', $workspaceId)->first();
        if (!$workspace) {
            return null;
        }
        return $workspace->pivot->role_id ? \App\Models\Role::find($workspace->pivot->role_id) : null;
    }

    public function create(User $user, Record $record): bool
    {
        $role = $this->getUserRole($user, $record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Owner, Admin, and Editor can create activities
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function signOff(User $user, Activity $activity): bool
    {
        $role = $this->getUserRole($user, $activity->record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Owner, Admin, and Editor can sign off
        return in_array($role->slug, ['owner', 'admin', 'editor']);
    }

    public function delete(User $user, Activity $activity): bool
    {
        $role = $this->getUserRole($user, $activity->record->collection->workspace_id);
        
        if (!$role) {
            return false;
        }

        // Owner and Admin can delete, or creator can delete their own
        return in_array($role->slug, ['owner', 'admin']) || $activity->created_by === $user->id;
    }
}
