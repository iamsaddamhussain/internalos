<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

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

        $limits = $workspace->getPlanLimits();
        $currentCounts = [
            'collections' => $workspace->collections()->count(),
            'records' => \App\Models\Record::whereIn('collection_id', $workspace->collections->pluck('id'))->count(),
            'users' => $workspace->users()->count(),
        ];

        return Inertia::render('Collections/Index', [
            'collections' => $collections,
            'userRole' => $userRole,
            'canCreate' => $userRole && in_array($userRole->slug, ['owner', 'admin', 'editor']),
            'currentPlan' => [
                'name' => $workspace->getPlanName(),
                'slug' => $workspace->plan,
                'limits' => $limits,
                'current' => $currentCounts,
            ],
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

        // Check plan limits
        if (!$workspace->canAddCollection()) {
            return back()->with('error', "You've reached your plan's collection limit. Upgrade to add more collections.");
        }

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
            'enable_import' => $request->enable_import ?? true,
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
            'enable_import' => $request->enable_import ?? true,
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

    public function downloadTemplate(Collection $collection)
    {
        $this->authorize('view', $collection);

        if (!$collection->enable_import) {
            abort(403, 'Import is not enabled for this collection');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setTitle(substr($collection->name, 0, 31)); // Excel limit
        
        // Add headers with styling
        $columnIndex = 1;
        $exampleRow = [];
        $instructionRow = [];
        
        foreach ($collection->schema['fields'] as $field) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
            
            // Header
            $sheet->setCellValue($columnLetter . '1', $field['label']);
            $sheet->getStyle($columnLetter . '1')->getFont()->setBold(true);
            $sheet->getStyle($columnLetter . '1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('4F46E5');
            $sheet->getStyle($columnLetter . '1')->getFont()->getColor()->setRGB('FFFFFF');
            
            // Instructions and examples based on field type
            $instruction = '';
            $example = '';
            
            switch ($field['type']) {
                case 'text':
                case 'email':
                    $instruction = 'Enter text';
                    $example = $field['type'] === 'email' ? 'user@example.com' : 'Sample text';
                    break;
                    
                case 'number':
                    $instruction = 'Enter number';
                    $example = '100';
                    break;
                    
                case 'date':
                    $instruction = 'Format: YYYY-MM-DD';
                    $example = date('Y-m-d');
                    break;
                    
                case 'checkbox':
                    $instruction = 'Enter: Yes or No';
                    $example = 'Yes';
                    break;
                    
                case 'select':
                    $options = $field['options'] ?? [];
                    $instruction = 'Choose: ' . implode(', ', array_slice($options, 0, 3));
                    if (count($options) > 3) $instruction .= '...';
                    $example = $options[0] ?? '';
                    break;
                    
                case 'relation':
                    $relatedCollection = Collection::find($field['relation_collection_id'] ?? null);
                    if ($relatedCollection) {
                        if ($field['multiple'] ?? false) {
                            $instruction = "Enter IDs separated by comma from {$relatedCollection->name}";
                            $example = '1, 2, 3';
                        } else {
                            $instruction = "Enter single ID from {$relatedCollection->name}";
                            $example = '1';
                        }
                    }
                    break;
            }
            
            // Add instruction row (row 2)
            $sheet->setCellValue($columnLetter . '2', $instruction);
            $sheet->getStyle($columnLetter . '2')->getFont()->setItalic(true);
            $sheet->getStyle($columnLetter . '2')->getFont()->getColor()->setRGB('666666');
            $sheet->getStyle($columnLetter . '2')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F3F4F6');
            
            // Add example row (row 3)
            $sheet->setCellValue($columnLetter . '3', $example);
            $sheet->getStyle($columnLetter . '3')->getFont()->setItalic(true);
            $sheet->getStyle($columnLetter . '3')->getFont()->getColor()->setRGB('059669');
            
            // Auto-size column
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            
            $columnIndex++;
        }
        
        // Add note
        $sheet->setCellValue('A5', 'Note: Delete rows 2-3 before importing. Row 2 shows instructions, Row 3 shows examples.');
        $sheet->getStyle('A5')->getFont()->setBold(true)->getColor()->setRGB('DC2626');
        $sheet->mergeCells('A5:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex - 1) . '5');
        
        // Create Excel file
        $filename = Str::slug($collection->name) . '-template-' . date('Y-m-d') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request, Collection $collection)
    {
        $this->authorize('create', [Record::class, $collection]);

        if (!$collection->enable_import) {
            return back()->with('error', 'Import is not enabled for this collection');
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            // Get headers from first row
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $headers[] = $sheet->getCell($columnLetter . '1')->getValue();
            }
            
            // Map headers to field IDs
            $fieldMap = [];
            foreach ($collection->schema['fields'] as $field) {
                $headerIndex = array_search($field['label'], $headers);
                if ($headerIndex !== false) {
                    $fieldMap[$headerIndex] = $field;
                }
            }
            
            // Import records (skip first row - headers)
            $imported = 0;
            $errors = [];
            $skipped = 0;
            
            for ($row = 2; $row <= $highestRow; $row++) {
                // Skip empty rows
                $isEmpty = true;
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $cellValue = $sheet->getCell($columnLetter . $row)->getValue();
                    if ($cellValue !== null && $cellValue !== '') {
                        $isEmpty = false;
                        break;
                    }
                }
                
                if ($isEmpty) {
                    $skipped++;
                    continue;
                }
                
                $data = [];
                $hasError = false;
                
                foreach ($fieldMap as $colIndex => $field) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $value = $sheet->getCell($columnLetter . $row)->getValue();
                    
                    // Process value based on field type
                    try {
                        $processedValue = $this->processImportValue($value, $field);
                        
                        // Validate required fields
                        if (($field['required'] ?? false) && ($processedValue === null || $processedValue === '')) {
                            $errors[] = "Row {$row}: {$field['label']} is required";
                            $hasError = true;
                            continue;
                        }
                        
                        $data[$field['id']] = $processedValue;
                    } catch (\Exception $e) {
                        $errors[] = "Row {$row}, Column '{$field['label']}': {$e->getMessage()}";
                        $hasError = true;
                    }
                }
                
                if (!$hasError && !empty($data)) {
                    try {
                        Record::create([
                            'collection_id' => $collection->id,
                            'created_by' => auth()->id(),
                            'data' => $data,
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Row {$row}: Failed to create record - {$e->getMessage()}";
                    }
                }
            }
            
            $message = "{$imported} record(s) imported successfully";
            if ($skipped > 0) {
                $message .= ". {$skipped} empty row(s) skipped";
            }
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " error(s) occurred";
            }
            
            if ($imported > 0) {
                return redirect()->route('collections.show', $collection->id)
                    ->with('success', $message)
                    ->with('importErrors', $errors);
            } else {
                return back()->with('error', 'No records were imported. ' . $message)
                    ->with('importErrors', $errors);
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function processImportValue($value, $field)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        switch ($field['type']) {
            case 'text':
            case 'email':
                return trim((string) $value);
                
            case 'number':
                return is_numeric($value) ? (float) $value : null;
                
            case 'date':
                // Handle Excel date serial numbers
                if (is_numeric($value)) {
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                    return $date->format('Y-m-d');
                }
                return $value;
                
            case 'checkbox':
                $lower = strtolower(trim($value));
                return in_array($lower, ['yes', 'true', '1', 'on']) ? true : false;
                
            case 'select':
                $options = $field['options'] ?? [];
                if (in_array($value, $options)) {
                    return $value;
                }
                throw new \Exception("Invalid option: {$value}");
                
            case 'relation':
                if ($field['multiple'] ?? false) {
                    // Multiple IDs separated by comma
                    $ids = array_map('trim', explode(',', $value));
                    return array_map('intval', array_filter($ids));
                } else {
                    // Single ID
                    return (int) $value;
                }
                
            default:
                return $value;
        }
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
