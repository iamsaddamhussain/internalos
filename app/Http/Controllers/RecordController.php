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
                    if (!($field['required'] ?? false)) {
                        $fieldRules = ['nullable', 'exists:records,id'];
                    } else {
                        $fieldRules = ['required', 'exists:records,id'];
                    }
                    break;
            }

            $rules[$field['id']] = $fieldRules;
        }

        return $rules;
    }
}
