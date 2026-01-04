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
            'enable_search' => $request->enable_search ?? true,
            'enable_export' => $request->enable_export ?? true,
            'per_page' => $request->per_page ?? 10,
            'schema' => [
                'fields' => $request->fields,
            ],
        ]);

        return redirect()->route('collections.show', $collection->id)
            ->with('success', 'Collection created successfully!');
    }

    public function show(Request $request, Collection $collection)
    {
        $this->authorize('view', $collection);

        $search = $request->get('search', '');
        $perPage = $request->get('per_page', $collection->per_page ?? 10);

        $query = $collection->records()->with('creator');

        // Apply search if enabled and search query exists
        if ($collection->enable_search && $search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(CAST(data AS TEXT)) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        $records = $query->latest()->paginate($perPage)->withQueryString();

        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $collection->workspace_id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? \App\Models\Role::with('permissions')->find($userWorkspace->pivot->role_id) 
            : null;

        // Check actual permissions from role
        $permissions = [];
        if ($userRole) {
            $permissions = $userRole->permissions->pluck('slug')->toArray();
        }

        $relatedData = $this->getRelatedDataForDisplay($collection);

        return Inertia::render('Collections/Show', [
            'collection' => $collection,
            'records' => $records,
            'userRole' => $userRole,
            'canCreate' => in_array('records.create', $permissions),
            'canView' => in_array('records.view', $permissions),
            'canEdit' => in_array('records.update', $permissions),
            'canDelete' => in_array('records.delete', $permissions),
            'relatedData' => $relatedData,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
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
            'enable_search' => $request->enable_search ?? true,
            'enable_export' => $request->enable_export ?? true,
            'per_page' => $request->per_page ?? 10,
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

    public function export(Collection $collection)
    {
        $this->authorize('view', $collection);

        if (!$collection->enable_export) {
            abort(403, 'Export is not enabled for this collection');
        }

        $records = $collection->records()->get();

        // Prepare CSV data
        $csvData = [];
        $headers = [];
        
        // Build headers from schema
        foreach ($collection->schema['fields'] as $field) {
            $headers[] = $field['label'];
        }
        $csvData[] = $headers;

        // Add records
        foreach ($records as $record) {
            $row = [];
            foreach ($collection->schema['fields'] as $field) {
                $value = $record->data[$field['id']] ?? '';
                
                // Handle different field types
                if ($field['type'] === 'checkbox') {
                    $value = $value ? 'Yes' : 'No';
                } elseif ($field['type'] === 'relation' && is_array($value)) {
                    $value = implode(', ', $value);
                } elseif (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                $row[] = $value;
            }
            $csvData[] = $row;
        }

        // Create CSV file
        $filename = Str::slug($collection->name) . '-' . date('Y-m-d') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
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
