<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index()
    {
        $workspace = app('workspace');
        
        // Check if user has permission to view members
        abort_unless(auth()->user()->hasPermission('members.view'), 403);

        $members = $workspace->users()->with('workspaces')->get()->map(function ($user) use ($workspace) {
            $pivot = $user->workspaces->where('id', $workspace->id)->first()->pivot;
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $pivot->role_id,
                'role' => Role::find($pivot->role_id),
                'joined_at' => $pivot->created_at,
            ];
        });

        $invitations = $workspace->invitations()
            ->with(['role', 'inviter'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get();

        $roles = Role::whereNull('workspace_id')->get();

        return Inertia::render('Members/Index', [
            'members' => $members,
            'invitations' => $invitations,
            'roles' => $roles,
            'currentUserId' => auth()->id(),
        ]);
    }

    public function updateRole(Request $request, $userId)
    {
        $workspace = app('workspace');
        
        // Check if user has permission to manage members
        abort_unless(auth()->user()->hasPermission('members.manage'), 403);

        // Prevent changing own role
        if (auth()->id() == $userId) {
            return back()->withErrors(['error' => 'You cannot change your own role.']);
        }

        // Prevent changing the owner's role
        if ($workspace->owner_id == $userId) {
            return back()->withErrors(['error' => 'Cannot change the workspace owner\'s role.']);
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $workspace->users()->updateExistingPivot($userId, [
            'role_id' => $request->role_id,
        ]);

        return back()->with('success', 'Member role updated successfully!');
    }

    public function destroy($userId)
    {
        $workspace = app('workspace');
        
        // Check if user has permission to manage members (includes removing)
        abort_unless(auth()->user()->hasPermission('members.manage'), 403);

        // Prevent removing yourself
        if (auth()->id() == $userId) {
            return back()->withErrors(['error' => 'You cannot remove yourself from the workspace.']);
        }

        // Prevent removing the owner
        if ($workspace->owner_id == $userId) {
            return back()->withErrors(['error' => 'Cannot remove the workspace owner.']);
        }

        $workspace->users()->detach($userId);

        return back()->with('success', 'Member removed from workspace.');
    }
}
