<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetWorkspace
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Super admins bypass workspace middleware
        if ($request->user() && $request->user()->is_super_admin) {
            return $next($request);
        }

        $workspaceId = session('workspace_id');

        if (!$workspaceId) {
            return redirect()->route('dashboard')->with('error', 'Please select a workspace first.');
        }

        $workspace = Workspace::find($workspaceId);

        if (!$workspace || !$request->user()->workspaces->contains($workspace)) {
            session()->forget('workspace_id');
            return redirect()->route('dashboard')->with('error', 'Invalid workspace. Please select a workspace.');
        }

        app()->instance('workspace', $workspace);

        return $next($request);
    }
}
