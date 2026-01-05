<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\Collection;
use App\Models\Record;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaasAdminController extends Controller
{
    public function dashboard()
    {
        // Check if user is super admin
        abort_unless(auth()->user()->is_super_admin, 403, 'Unauthorized access to SAAS Admin Dashboard');

        // Get all workspaces with owner info
        $workspaces = Workspace::with(['users' => function($query) {
            $query->withPivot('role_id');
        }])->withCount(['collections', 'users'])->get()->map(function($workspace) {
            $owner = $workspace->users->first(function($user) use ($workspace) {
                $role = $user->workspaces()->where('workspaces.id', $workspace->id)->first();
                if ($role) {
                    $roleModel = \App\Models\Role::find($role->pivot->role_id);
                    return $roleModel && $roleModel->slug === 'owner';
                }
                return false;
            });

            return [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'created_at' => $workspace->created_at->format('M d, Y'),
                'collections_count' => $workspace->collections_count,
                'members_count' => $workspace->users_count,
                'owner_name' => $owner ? $owner->name : 'N/A',
                'owner_email' => $owner ? $owner->email : 'N/A',
            ];
        });

        // Get all users
        $users = User::with('workspaces')->latest()->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'workspaces_count' => $user->workspaces->count(),
                'created_at' => $user->created_at->format('M d, Y'),
            ];
        });

        // System-wide stats
        $stats = [
            'total_users' => User::count(),
            'total_workspaces' => Workspace::count(),
            'total_collections' => Collection::count(),
            'total_records' => Record::count(),
            'total_activities' => Activity::count(),
            'users_last_30_days' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'workspaces_last_30_days' => Workspace::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // Recent activity
        $recentUsers = User::latest()->take(5)->get()->map(function($user) {
            return [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->diffForHumans(),
            ];
        });

        $recentWorkspaces = Workspace::latest()->take(5)->get()->map(function($workspace) {
            return [
                'name' => $workspace->name,
                'created_at' => $workspace->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('Admin/SaasDashboard', [
            'workspaces' => $workspaces,
            'users' => $users,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentWorkspaces' => $recentWorkspaces,
        ]);
    }
}
