<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class WorkspaceController extends Controller
{
    public function index()
    {
        $workspaces = auth()->user()->workspaces;

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function create()
    {
        return Inertia::render('Workspaces/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace = Workspace::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(4),
            'owner_id' => auth()->id(),
        ]);

        // Get the Owner role (system role)
        $ownerRole = \App\Models\Role::where('slug', 'owner')
            ->whereNull('workspace_id')
            ->first();

        $workspace->users()->attach(auth()->id(), ['role_id' => $ownerRole?->id]);

        // Set as active workspace
        session(['workspace_id' => $workspace->id]);

        return redirect()->route('dashboard');
    }

    public function switch(Request $request, Workspace $workspace)
    {
        // Verify user has access to this workspace
        abort_unless($request->user()->workspaces->contains($workspace), 403);

        session(['workspace_id' => $workspace->id]);

        return redirect()->route('dashboard');
    }
}
