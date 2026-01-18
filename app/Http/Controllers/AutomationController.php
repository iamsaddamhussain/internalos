<?php

namespace App\Http\Controllers;

use App\Models\Automation;
use App\Models\Workspace;
use App\Models\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AutomationController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of automations for a workspace.
     */
    public function index(Request $request): Response
    {
        $workspace = app('workspace');
        $this->authorize('viewAny', [Automation::class, $workspace]);

        $automations = Automation::with(['collection', 'triggers', 'conditions', 'actions'])
            ->forWorkspace($workspace->id)
            ->when($request->collection_id, function ($query) use ($request) {
                $query->forCollection($request->collection_id);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Automations/Index', [
            'workspace' => $workspace,
            'automations' => $automations,
            'collections' => $workspace->collections()->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new automation.
     */
    public function create(Collection $collection): Response
    {
        $workspace = app('workspace');
        $this->authorize('create', [Automation::class, $workspace]);

        // Get collection fields for building triggers/conditions
        $fields = $collection->schema['fields'] ?? [];

        return Inertia::render('Automations/Create', [
            'workspace' => $workspace,
            'collection' => $collection,
            'fields' => $fields,
        ]);
    }

    /**
     * Store a newly created automation.
     */
    public function store(Request $request, Collection $collection)
    {
        $workspace = app('workspace');
        $this->authorize('create', [Automation::class, $workspace]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'triggers' => 'required|array|min:1',
            'triggers.*.type' => 'required|string|in:record_created,record_updated,date_reached,comment_added,status_changed',
            'triggers.*.field_name' => 'nullable|string',
            'triggers.*.offset_days' => 'nullable|integer',
            'conditions' => 'nullable|array',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => 'required|string|in:=,!=,>,<,>=,<=,contains,not_contains',
            'conditions.*.value' => 'required',
            'conditions.*.condition_group' => 'nullable|string',
            'actions' => 'required|array|min:1',
            'actions.*.type' => 'required|string|in:notify,email,update_field,create_record',
            'actions.*.target' => 'nullable|string',
            'actions.*.channel' => 'nullable|string|in:in_app,email,both',
            'actions.*.config' => 'nullable|array',
        ]);

        $automation = Automation::create([
            'workspace_id' => $workspace->id,
            'collection_id' => $collection->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create triggers
        foreach ($validated['triggers'] as $triggerData) {
            $automation->triggers()->create($triggerData);
        }

        // Create conditions
        if (!empty($validated['conditions'])) {
            foreach ($validated['conditions'] as $conditionData) {
                $automation->conditions()->create($conditionData);
            }
        }

        // Create actions
        foreach ($validated['actions'] as $index => $actionData) {
            $actionData['order'] = $index;
            $automation->actions()->create($actionData);
        }

        return redirect()
            ->route('automations.index')
            ->with('success', 'Automation created successfully!');
    }

    /**
     * Display the specified automation.
     */
    public function show(Automation $automation): Response
    {
        $workspace = app('workspace');
        $this->authorize('view', [$automation, $workspace]);

        $automation->load(['collection', 'triggers', 'conditions', 'actions', 'logs' => function ($query) {
            $query->latest()->limit(50);
        }]);

        return Inertia::render('Automations/Show', [
            'workspace' => $workspace,
            'automation' => $automation,
        ]);
    }

    /**
     * Show the form for editing the specified automation.
     */
    public function edit(Automation $automation): Response
    {
        $workspace = app('workspace');
        $this->authorize('update', [$automation, $workspace]);

        $automation->load(['collection', 'triggers', 'conditions', 'actions']);
        $fields = $automation->collection->schema['fields'] ?? [];

        return Inertia::render('Automations/Edit', [
            'workspace' => $workspace,
            'automation' => $automation,
            'collection' => $automation->collection,
            'fields' => $fields,
        ]);
    }

    /**
     * Update the specified automation.
     */
    public function update(Request $request, Automation $automation)
    {
        $workspace = app('workspace');
        $this->authorize('update', [$automation, $workspace]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'triggers' => 'required|array|min:1',
            'triggers.*.type' => 'required|string|in:record_created,record_updated,date_reached,comment_added,status_changed',
            'triggers.*.field_name' => 'nullable|string',
            'triggers.*.offset_days' => 'nullable|integer',
            'conditions' => 'nullable|array',
            'conditions.*.field' => 'required|string',
            'conditions.*.operator' => 'required|string|in:=,!=,>,<,>=,<=,contains,not_contains',
            'conditions.*.value' => 'required',
            'conditions.*.condition_group' => 'nullable|string',
            'actions' => 'required|array|min:1',
            'actions.*.type' => 'required|string|in:notify,email,update_field,create_record',
            'actions.*.target' => 'nullable|string',
            'actions.*.channel' => 'nullable|string|in:in_app,email,both',
            'actions.*.config' => 'nullable|array',
        ]);

        $automation->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Delete and recreate triggers
        $automation->triggers()->delete();
        foreach ($validated['triggers'] as $triggerData) {
            $automation->triggers()->create($triggerData);
        }

        // Delete and recreate conditions
        $automation->conditions()->delete();
        if (!empty($validated['conditions'])) {
            foreach ($validated['conditions'] as $conditionData) {
                $automation->conditions()->create($conditionData);
            }
        }

        // Delete and recreate actions
        $automation->actions()->delete();
        foreach ($validated['actions'] as $index => $actionData) {
            $actionData['order'] = $index;
            $automation->actions()->create($actionData);
        }

        return redirect()
            ->route('automations.show', $automation)
            ->with('success', 'Automation updated successfully!');
    }

    /**
     * Toggle automation active status.
     */
    public function toggle(Automation $automation)
    {
        $workspace = app('workspace');
        $this->authorize('update', [$automation, $workspace]);

        $automation->update([
            'is_active' => !$automation->is_active,
        ]);

        return back()->with('success', $automation->is_active ? 'Automation activated!' : 'Automation deactivated!');
    }

    /**
     * Remove the specified automation.
     */
    public function destroy(Automation $automation)
    {
        $workspace = app('workspace');
        $this->authorize('delete', [$automation, $workspace]);

        $automation->delete();

        return redirect()
            ->route('automations.index')
            ->with('success', 'Automation deleted successfully!');
    }
}
