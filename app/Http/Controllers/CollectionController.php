<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CollectionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $workspace = app('workspace');
        $collections = $workspace->collections;

        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $workspace->id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id) 
            : null;

        return Inertia::render('Collections/Index', [
            'collections' => $collections,
            'userRole' => $userRole,
            'canCreate' => $userRole && in_array($userRole->slug, ['owner', 'admin', 'editor']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Collection::class);
        
        $workspace = app('workspace');
        $collections = $workspace->collections;
        
        return Inertia::render('Collections/Create', [
            'collections' => $collections,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Collection::class);
        
        $workspace = app('workspace');

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'fields' => 'required|array|min:1',
            'fields.*.id' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.required' => 'boolean',
            'fields.*.options' => 'array',
        ]);

        $collection = Collection::create([
            'workspace_id' => $workspace->id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon ?? '📄',
            'schema' => [
                'fields' => $request->fields,
            ],
        ]);

        return redirect()->route('collections.show', $collection->id)
            ->with('success', 'Collection created successfully!');
    }

    public function show(Collection $collection)
    {
        $this->authorize('view', $collection);

        $records = $collection->records()->with('creator')->latest()->get();

        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $collection->workspace_id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? \App\Models\Role::find($userWorkspace->pivot->role_id) 
            : null;

        $relatedData = $this->getRelatedDataForDisplay($collection);

        return Inertia::render('Collections/Show', [
            'collection' => $collection,
            'records' => $records,
            'userRole' => $userRole,
            'canCreate' => $userRole && in_array($userRole->slug, ['owner', 'admin', 'editor']),
            'canEdit' => $userRole && in_array($userRole->slug, ['owner', 'admin', 'editor']),
            'canDelete' => $userRole && in_array($userRole->slug, ['owner', 'admin']),
            'relatedData' => $relatedData,
        ]);
    }

    public function edit(Collection $collection)
    {
        $this->authorize('update', $collection);

        $workspace = app('workspace');
        $collections = $workspace->collections;

        return Inertia::render('Collections/Edit', [
            'collection' => $collection,
            'collections' => $collections,
        ]);
    }

    public function update(Request $request, Collection $collection)
    {
        $this->authorize('update', $collection);

        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'fields' => 'required|array|min:1',
        ]);

        $collection->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon ?? '📄',
            'schema' => [
                'fields' => $request->fields,
            ],
        ]);

        return redirect()->route('collections.show', $collection->id)
            ->with('success', 'Collection updated successfully!');
    }

    public function destroy(Collection $collection)
    {
        $this->authorize('delete', $collection);

        $collection->delete();

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully!');
    }

    private function getRelatedDataForDisplay(Collection $collection)
    {
        $relatedData = [];
        
        foreach ($collection->schema['fields'] as $field) {
            if ($field['type'] === 'relation' && isset($field['relation_collection_id'])) {
                $relatedCollection = Collection::find($field['relation_collection_id']);
                if ($relatedCollection) {
                    $records = $relatedCollection->records()->get()->map(function ($record) use ($relatedCollection) {
                        $displayField = collect($relatedCollection->schema['fields'])
                            ->first(fn($f) => $f['type'] === 'text');
                        
                        if (!$displayField) {
                            $displayField = $relatedCollection->schema['fields'][0] ?? null;
                        }
                        
                        $displayValue = $displayField 
                            ? ($record->data[$displayField['id']] ?? 'Untitled')
                            : 'Untitled';
                        
                        return [
                            'id' => $record->id,
                            'display' => $displayValue,
                        ];
                    });
                    
                    $relatedData[$field['id']] = [
                        'collection' => $relatedCollection->only(['id', 'name']),
                        'records' => $records,
                    ];
                }
            }
        }
        
        return $relatedData;
    }
}
