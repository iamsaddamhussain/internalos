<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Record;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RecordController extends Controller
{
    use AuthorizesRequests;

    public function show(Collection $collection, Record $record)
    {
        $this->authorize('view', $record);

        // Load activities with relationships
        $activities = $record->activities()
            ->with(['creator', 'signedOffBy'])
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'status' => $activity->status,
                    'created_by' => $activity->creator->name,
                    'created_at' => $activity->created_at->diffForHumans(),
                    'signed_off_by' => $activity->signedOffBy?->name,
                    'signed_off_at' => $activity->signed_off_at?->diffForHumans(),
                    'is_signed_off' => $activity->isSignedOff(),
                ];
            });

        // Get display label for the record
        $displayField = collect($collection->schema['fields'])
            ->first(fn($f) => $f['type'] === 'text');
        
        $recordTitle = $displayField 
            ? ($record->data[$displayField['id']] ?? 'Untitled')
            : 'Untitled';

        // Resolve relation field values
        $relatedData = $this->getRelatedData($collection);
        $displayData = [];
        
        foreach ($collection->schema['fields'] as $field) {
            $value = $record->data[$field['id']] ?? null;
            
            if ($field['type'] === 'relation' && $value !== null && isset($relatedData[$field['id']])) {
                // Check if multiple selection
                if (!empty($field['multiple']) && is_array($value)) {
                    $displayNames = [];
                    foreach ($value as $id) {
                        $relatedRecord = collect($relatedData[$field['id']]['records'])->firstWhere('id', $id);
                        if ($relatedRecord) {
                            $displayNames[] = $relatedRecord['display'];
                        }
                    }
                    $displayData[$field['id']] = implode(', ', $displayNames);
                } else {
                    // Single selection
                    $relatedRecord = collect($relatedData[$field['id']]['records'])->firstWhere('id', $value);
                    $displayData[$field['id']] = $relatedRecord ? $relatedRecord['display'] : $value;
                }
            } else if ($field['type'] === 'checkbox') {
                $displayData[$field['id']] = $value ? 'Yes' : 'No';
            } else if ($field['type'] === 'date' && $value) {
                $displayData[$field['id']] = \Carbon\Carbon::parse($value)->format('M d, Y');
            } else {
                $displayData[$field['id']] = $value;
            }
        }

        // Get user permissions
        $userWorkspace = auth()->user()->workspaces()->where('workspaces.id', $collection->workspace_id)->first();
        $userRole = $userWorkspace && $userWorkspace->pivot->role_id 
            ? \App\Models\Role::with('permissions')->find($userWorkspace->pivot->role_id) 
            : null;

        $permissions = [];
        if ($userRole) {
            $permissions = $userRole->permissions->pluck('slug')->toArray();
        }

        return Inertia::render('Records/Show', [
            'collection' => $collection,
            'record' => $record,
            'recordTitle' => $recordTitle,
            'displayData' => $displayData,
            'activities' => $activities,
            'canEdit' => in_array('records.update', $permissions),
            'canDelete' => in_array('records.delete', $permissions),
            'canCreateActivity' => in_array('activities.create', $permissions),
            'canSignOffActivity' => in_array('activities.signoff', $permissions),
            'canDeleteActivity' => in_array('activities.delete', $permissions),
        ]);
    }

    public function create(Collection $collection)
    {
        $this->authorize('create', [Record::class, $collection]);

        $relatedData = $this->getRelatedData($collection);

        return Inertia::render('Records/Create', [
            'collection' => $collection,
            'relatedData' => $relatedData,
        ]);
    }

    public function store(Request $request, Collection $collection)
    {
        $this->authorize('create', [Record::class, $collection]);

        // Validate based on collection schema
        $rules = $this->buildValidationRules($collection->schema['fields']);
        $validated = $request->validate($rules);

        $record = Record::create([
            'collection_id' => $collection->id,
            'data' => $validated,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('collections.show', $collection->id)->with('success', 'Record created successfully!');
    }

    public function edit(Collection $collection, Record $record)
    {
        $this->authorize('update', $record);

        $relatedData = $this->getRelatedData($collection);

        return Inertia::render('Records/Edit', [
            'collection' => $collection,
            'record' => $record,
            'relatedData' => $relatedData,
        ]);
    }

    public function update(Request $request, Collection $collection, Record $record)
    {
        $this->authorize('update', $record);

        // Validate based on collection schema
        $rules = $this->buildValidationRules($collection->schema['fields']);
        $validated = $request->validate($rules);

        $record->update([
            'data' => $validated,
        ]);

        return redirect()->route('collections.show', $collection->id)->with('success', 'Record updated successfully!');
    }

    public function destroy(Collection $collection, Record $record)
    {
        $this->authorize('delete', $record);

        $record->delete();

        return back()->with('success', 'Record deleted successfully!');
    }

    private function getRelatedData(Collection $collection)
    {
        $relatedData = [];
        
        foreach ($collection->schema['fields'] as $field) {
            if ($field['type'] === 'relation' && isset($field['relation_collection_id'])) {
                $relatedCollection = Collection::find($field['relation_collection_id']);
                if ($relatedCollection) {
                    $records = $relatedCollection->records()->get()->map(function ($record) use ($relatedCollection) {
                        // Get display field (first text field or first field)
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

    private function buildValidationRules(array $fields)
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field['type']) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:500';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
                case 'select':
                    if (!empty($field['options'])) {
                        $fieldRules[] = 'in:' . implode(',', $field['options']);
                    }
                    break;
                case 'relation':
                    // For relation fields, validate that the ID exists in the related collection's records
                    // Check if multiple selection is enabled
                    if (!empty($field['multiple'])) {
                        // Multiple selection
                        if (!($field['required'] ?? false)) {
                            $fieldRules = ['nullable', 'array'];
                        } else {
                            $fieldRules = ['required', 'array'];
                        }
                        $fieldRules[] = 'min:1';
                        $rules[$field['id']] = $fieldRules;
                        $rules[$field['id'] . '.*'] = ['exists:records,id'];
                    } else {
                        // Single selection
                        if (!($field['required'] ?? false)) {
                            $fieldRules = ['nullable', 'exists:records,id'];
                        } else {
                            $fieldRules = ['required', 'exists:records,id'];
                        }
                    }
                    break;
            }

            $rules[$field['id']] = $fieldRules;
        }

        return $rules;
    }
}
