<?php

use App\Http\Controllers\CollectionController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\WorkspaceController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Public invitation routes
Route::get('/invitations/{token}/accept', [InvitationController::class, 'show'])->name('invitations.show');
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');

Route::get('/dashboard', function () {
    $workspace = session('workspace_id') ? \App\Models\Workspace::find(session('workspace_id')) : null;
    $userRole = null;
    $stats = [
        'total_collections' => 0,
        'total_records' => 0,
        'total_members' => 0,
    ];
    $recentCollections = [];
    
    if ($workspace) {
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id) 
            : null;
        
        // Get statistics
        $collections = \App\Models\Collection::where('workspace_id', $workspace->id)->get();
        $totalRecords = \App\Models\Record::whereIn('collection_id', $collections->pluck('id'))->count();
        $totalMembers = $workspace->users()->count();
        
        $stats = [
            'total_collections' => $collections->count(),
            'total_records' => $totalRecords,
            'total_members' => $totalMembers,
        ];
        
        // Get recent collections
        $recentCollections = $collections->sortByDesc('created_at')->take(5)->map(function ($collection) {
            $recordCount = \App\Models\Record::where('collection_id', $collection->id)->count();
            $collectionArray = [
                'id' => $collection->id,
                'name' => $collection->name,
                'record_count' => $recordCount,
                'field_count' => count($collection->schema['fields'] ?? []),
                'created_at' => $collection->created_at->diffForHumans(),
            ];
            
            // Only add description and icon if columns exist
            if (isset($collection->description)) {
                $collectionArray['description'] = $collection->description;
            }
            if (isset($collection->icon)) {
                $collectionArray['icon'] = $collection->icon;
            }
            
            return $collectionArray;
        })->values();
    }
    
    return Inertia::render('Dashboard', [
        'userRole' => $userRole,
        'stats' => $stats,
        'recentCollections' => $recentCollections,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Workspace routes
    Route::resource('workspaces', WorkspaceController::class)->only(['index', 'create', 'store']);
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    // Collection routes (require active workspace)
    Route::middleware('workspace')->group(function () {
        Route::resource('collections', CollectionController::class);
        
        // Member management
        Route::resource('members', MemberController::class)->only(['index', 'destroy']);
        Route::put('/members/{user}/role', [MemberController::class, 'updateRole'])->name('members.update-role');

        // Invitations
        Route::resource('invitations', InvitationController::class)->only(['create', 'store', 'destroy']);
        
        // Record routes
        Route::get('/collections/{collection}/records/create', [RecordController::class, 'create'])->name('records.create');
        Route::post('/collections/{collection}/records', [RecordController::class, 'store'])->name('records.store');
        Route::get('/collections/{collection}/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
        Route::put('/collections/{collection}/records/{record}', [RecordController::class, 'update'])->name('records.update');
        Route::delete('/collections/{collection}/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
        
        // Admin - Permission Management (Owner only)
        Route::get('/admin/permissions', [RolePermissionController::class, 'index'])->name('admin.permissions');
        Route::put('/admin/permissions/{role}', [RolePermissionController::class, 'updateRolePermissions'])->name('admin.permissions.update');
    });
});

require __DIR__.'/auth.php';
