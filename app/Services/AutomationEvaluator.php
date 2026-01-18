<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\Notification;
use App\Models\Record;
use Illuminate\Support\Facades\Log;

class AutomationEvaluator
{
    /**
     * Evaluate and execute an automation for a given record.
     */
    public function evaluate(Automation $automation, Record $record): bool
    {
        try {
            // Check if automation is active
            if (!$automation->is_active) {
                AutomationLog::logSkipped(
                    $automation->id,
                    $record->id,
                    'Automation is not active'
                );
                return false;
            }

            // Evaluate all conditions
            if (!$this->evaluateConditions($automation, $record)) {
                AutomationLog::logSkipped(
                    $automation->id,
                    $record->id,
                    'Conditions not met'
                );
                return false;
            }

            // Execute all actions
            $this->executeActions($automation, $record);

            AutomationLog::logSuccess(
                $automation->id,
                $record->id,
                'Automation executed successfully'
            );

            return true;

        } catch (\Exception $e) {
            AutomationLog::logFailed(
                $automation->id,
                $record->id,
                'Automation execution failed: ' . $e->getMessage(),
                ['exception' => $e->getTraceAsString()]
            );

            Log::error('Automation execution failed', [
                'automation_id' => $automation->id,
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Evaluate all conditions for an automation.
     */
    protected function evaluateConditions(Automation $automation, Record $record): bool
    {
        $conditions = $automation->conditions;

        if ($conditions->isEmpty()) {
            return true; // No conditions = always execute
        }

        // Group conditions by condition_group for AND/OR logic
        $groups = $conditions->groupBy('condition_group');

        // All groups must pass (AND between groups)
        foreach ($groups as $groupName => $groupConditions) {
            $groupPassed = false;

            // Any condition in the group can pass (OR within group)
            foreach ($groupConditions as $condition) {
                $fieldValue = $this->getFieldValue($record, $condition->field);
                
                if ($condition->evaluate($fieldValue)) {
                    $groupPassed = true;
                    break; // OR logic - one success is enough
                }
            }

            if (!$groupPassed) {
                return false; // AND logic - all groups must pass
            }
        }

        return true;
    }

    /**
     * Get field value from record data.
     */
    protected function getFieldValue(Record $record, string $fieldName)
    {
        // Check if it's a system field
        if (in_array($fieldName, ['id', 'created_at', 'updated_at'])) {
            return $record->{$fieldName};
        }

        // Get from record data JSON
        return $record->data[$fieldName] ?? null;
    }

    /**
     * Execute all actions for an automation.
     */
    protected function executeActions(Automation $automation, Record $record): void
    {
        $actions = $automation->actions;

        foreach ($actions as $action) {
            try {
                match ($action->type) {
                    'notify' => $this->executeNotifyAction($action, $automation, $record),
                    'email' => $this->executeEmailAction($action, $automation, $record),
                    'update_field' => $this->executeUpdateFieldAction($action, $record),
                    'create_record' => $this->executeCreateRecordAction($action, $automation, $record),
                    default => Log::warning("Unknown action type: {$action->type}"),
                };
            } catch (\Exception $e) {
                Log::error('Action execution failed', [
                    'action_id' => $action->id,
                    'action_type' => $action->type,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Execute notify action.
     */
    protected function executeNotifyAction($action, Automation $automation, Record $record): void
    {
        $users = $this->resolveTargetUsers($action->target, $record);

        $title = $action->config['title'] ?? $automation->name;
        $body = $action->config['body'] ?? $this->generateNotificationBody($automation, $record);
        
        // Replace template variables in title and body
        $title = $this->replaceTemplateVariables($title, $record);
        $body = $this->replaceTemplateVariables($body, $record);
        
        $metadata = [
            'record_id' => $record->id,
            'collection_id' => $record->collection_id,
            'link' => "/workspaces/{$automation->workspace_id}/collections/{$record->collection_id}/records/{$record->id}",
        ];

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'workspace_id' => $automation->workspace_id,
                'automation_id' => $automation->id,
                'record_id' => $record->id,
                'type' => 'automation',
                'title' => $title,
                'body' => $body,
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Execute email action (placeholder for future implementation).
     */
    protected function executeEmailAction($action, Automation $automation, Record $record): void
    {
        $users = $this->resolveTargetUsers($action->target, $record);

        $title = $action->config['title'] ?? $automation->name;
        $body = $action->config['body'] ?? $this->generateNotificationBody($automation, $record);
        
        // Replace template variables
        $title = $this->replaceTemplateVariables($title, $record);
        $body = $this->replaceTemplateVariables($body, $record);

        foreach ($users as $user) {
            try {
                \Mail::to($user->email)->queue(
                    new \App\Mail\AutomationNotification($title, $body, $record, $automation)
                );
                
                Log::info('Email queued successfully', [
                    'automation_id' => $automation->id,
                    'record_id' => $record->id,
                    'recipient' => $user->email,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to queue email', [
                    'automation_id' => $automation->id,
                    'record_id' => $record->id,
                    'recipient' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Execute update field action.
     */
    protected function executeUpdateFieldAction($action, Record $record): void
    {
        $fieldName = $action->config['field'] ?? null;
        $newValue = $action->config['value'] ?? null;

        if ($fieldName && $newValue !== null) {
            $data = $record->data;
            $data[$fieldName] = $newValue;
            $record->update(['data' => $data]);
        }
    }

    /**
     * Execute create record action.
     */
    protected function executeCreateRecordAction($action, Automation $automation, Record $record): void
    {
        // TODO: Implement record creation
        Log::info('Create record action triggered', [
            'automation_id' => $automation->id,
            'record_id' => $record->id,
        ]);
    }

    /**
     * Resolve target users based on target specification.
     */
    protected function resolveTargetUsers(string $target, Record $record): array
    {
        $users = [];

        // Handle multiple targets separated by comma
        $targets = array_map('trim', explode(',', $target));

        foreach ($targets as $singleTarget) {
            if (str_starts_with($singleTarget, 'field:')) {
                // Field reference: field:assignee
                $fieldName = substr($singleTarget, 6);
                $userId = $record->data[$fieldName] ?? null;
                
                if ($userId) {
                    $user = \App\Models\User::find($userId);
                    if ($user) {
                        $users[] = $user;
                    }
                }
            } elseif (str_starts_with($singleTarget, 'role:')) {
                // Role reference: role:manager
                $roleName = substr($singleTarget, 5);
                // Get users with this role in the workspace
                $workspaceUsers = \App\Models\Workspace::find($record->collection->workspace_id)
                    ->users()
                    ->whereHas('roles', function ($query) use ($roleName) {
                        $query->where('name', $roleName);
                    })
                    ->get();
                $users = array_merge($users, $workspaceUsers->all());
            } elseif (is_numeric($singleTarget)) {
                // Direct user ID
                $user = \App\Models\User::find($singleTarget);
                if ($user) {
                    $users[] = $user;
                }
            } elseif ($singleTarget === 'creator') {
                // Record creator
                if ($record->created_by) {
                    $user = \App\Models\User::find($record->created_by);
                    if ($user) {
                        $users[] = $user;
                    }
                }
            }
        }

        return array_unique($users, SORT_REGULAR);
    }

    /**
     * Generate notification body from record data.
     */
    protected function generateNotificationBody(Automation $automation, Record $record): string
    {
        $collectionName = $record->collection->name ?? 'Record';
        return "Action required on {$collectionName}: {$this->getRecordTitle($record)}";
    }

    /**
     * Get record title from data.
     */
    protected function getRecordTitle(Record $record): string
    {
        // Try common title fields
        $titleFields = ['name', 'title', 'subject', 'description'];
        
        foreach ($titleFields as $field) {
            if (isset($record->data[$field])) {
                return $record->data[$field];
            }
        }

        return "Record #{$record->id}";
    }

    /**
     * Replace template variables in text with record field values.
     */
    protected function replaceTemplateVariables(string $text, Record $record): string
    {
        // Replace {{field_name}} with actual field values
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($record) {
            $fieldName = $matches[1];
            
            // Find the field in collection schema
            $field = collect($record->collection->schema['fields'] ?? [])
                ->firstWhere('id', $fieldName);
            
            if (!$field) {
                return $matches[0]; // Return original if field not found
            }
            
            $value = $record->data[$fieldName] ?? '';
            
            // Format based on field type
            if ($field['type'] === 'date' && $value) {
                return \Carbon\Carbon::parse($value)->format('M d, Y');
            }
            
            return $value;
        }, $text);
    }
}
