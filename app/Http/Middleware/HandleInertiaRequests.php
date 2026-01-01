<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $workspaceId = session('workspace_id');
        $currentWorkspace = null;
        
        if ($workspaceId && $request->user()) {
            $currentWorkspace = $request->user()
                ->workspaces()
                ->where('workspaces.id', $workspaceId)
                ->first();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'workspaces' => fn () => $request->user() 
                ? $request->user()->workspaces 
                : [],
            'currentWorkspace' => fn () => $currentWorkspace,
        ];
    }
}
