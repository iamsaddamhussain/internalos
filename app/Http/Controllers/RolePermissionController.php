<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolePermissionController extends Controller
{
    public function index()
    {
        $workspace = app('workspace');
        
        // Only Owner can manage permissions
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? Role::find($userWorkspace->pivot->role_id) 
            : null;
            
        abort_unless($userRole && $userRole->slug === 'owner', 403);

        $roles = Role::whereNull('workspace_id')->with('permissions')->get();
        $permissions = Permission::orderBy('module')->orderBy('name')->get();
        
        // Group permissions by module
        $groupedPermissions = $permissions->groupBy('module');

        return Inertia::render('Admin/Permissions', [
            'roles' => $roles,
            'permissions' => $groupedPermissions,
            'userRoleSlug' => $userRole->slug,
        ]);
    }

    public function updateRolePermissions(Request $request, Role $role)
    {
        $workspace = app('workspace');
        
        // Only Owner can manage permissions
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? Role::find($userWorkspace->pivot->role_id) 
            : null;
            
        abort_unless($userRole && $userRole->slug === 'owner', 403);

        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions);

        return back()->with('success', 'Role permissions updated successfully!');
    }
}
