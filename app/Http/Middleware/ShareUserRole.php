<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && session('workspace_id')) {
            $workspace = Workspace::find(session('workspace_id'));
            
            if ($workspace) {
                $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
                $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
                    ? Role::find($userWorkspace->pivot->role_id) 
                    : null;
                
                Inertia::share('userRole', $userRole);
            }
        }
        
        return $next($request);
    }
}
