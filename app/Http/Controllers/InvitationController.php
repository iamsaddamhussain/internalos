<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InvitationController extends Controller
{
    public function create()
    {
        $workspace = app('workspace');
        
        // Only Owner and Admin can invite members
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? Role::find($userWorkspace->pivot->role_id) 
            : null;
            
        abort_unless($userRole && in_array($userRole->slug, ['owner', 'admin']), 403);

        $roles = Role::whereNull('workspace_id')->get();

        return Inertia::render('Members/Invite', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $workspace = app('workspace');
        
        // Only Owner and Admin can invite members
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? Role::find($userWorkspace->pivot->role_id) 
            : null;
            
        abort_unless($userRole && in_array($userRole->slug, ['owner', 'admin']), 403);

        $request->validate([
            'email' => 'required|email',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Check if user is already a member
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser && $workspace->users->contains($existingUser)) {
            return back()->withErrors(['email' => 'This user is already a member of this workspace.']);
        }

        // Check if invitation already exists
        $existingInvitation = Invitation::where('workspace_id', $workspace->id)
            ->where('email', $request->email)
            ->whereNull('accepted_at')
            ->first();

        if ($existingInvitation) {
            if ($existingInvitation->isExpired()) {
                $existingInvitation->delete();
            } else {
                return back()->withErrors(['email' => 'An invitation has already been sent to this email.']);
            }
        }

        $invitation = Invitation::create([
            'workspace_id' => $workspace->id,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'invited_by' => auth()->id(),
        ]);

        // TODO: Send email notification
        // Mail::to($invitation->email)->send(new WorkspaceInvitation($invitation));

        return back()->with('success', 'Invitation sent successfully!');
    }

    public function show($token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('dashboard')->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')->with('error', 'This invitation has expired.');
        }

        return Inertia::render('Invitations/Accept', [
            'invitation' => $invitation->load(['workspace', 'role', 'inviter']),
        ]);
    }

    public function accept(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return redirect()->route('dashboard')->with('error', 'This invitation has already been accepted.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('login')->with('error', 'This invitation has expired.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create or find user
        $user = User::where('email', $invitation->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $invitation->email,
                'password' => Hash::make($request->password),
            ]);
        }

        // Add user to workspace with role
        $invitation->workspace->users()->attach($user->id, [
            'role_id' => $invitation->role_id,
        ]);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        // Log in the user
        auth()->login($user);

        // Set active workspace
        session(['workspace_id' => $invitation->workspace_id]);

        return redirect()->route('dashboard')->with('success', 'Welcome to ' . $invitation->workspace->name . '!');
    }

    public function destroy(Invitation $invitation)
    {
        $workspace = app('workspace');

        if ($invitation->workspace_id !== $workspace->id) {
            abort(403);
        }
        
        // Only Owner and Admin can cancel invitations
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? Role::find($userWorkspace->pivot->role_id) 
            : null;
            
        abort_unless($userRole && in_array($userRole->slug, ['owner', 'admin']), 403);

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled.');
    }
}
