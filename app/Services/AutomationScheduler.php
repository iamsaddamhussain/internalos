<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutomationScheduler
{
    protected AutomationEvaluator $evaluator;

    public function __construct(AutomationEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * Run all active date-based automations.
     */
    public function runDateBasedAutomations(): array
    {
        $results = [
            'total' => 0,
            'executed' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        // Get all active automations with date triggers
        $automations = Automation::with(['triggers', 'conditions', 'actions', 'collection'])
            ->active()
            ->whereHas('triggers', function ($query) {
                $query->where('type', 'date_reached');
            })
            ->get();

        foreach ($automations as $automation) {
            try {
                $automationResults = $this->processDateAutomation($automation);
                $results['total']++;
                $results['executed'] += $automationResults['executed'];
                $results['skipped'] += $automationResults['skipped'];
                $results['failed'] += $automationResults['failed'];
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error('Failed to process automation', [
                    'automation_id' => $automation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Process a single date-based automation.
     */
    protected function processDateAutomation(Automation $automation): array
    {
        $results = [
            'executed' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        // Get date triggers
        $dateTriggers = $automation->triggers->where('type', 'date_reached');

        foreach ($dateTriggers as $trigger) {
            // Find matching records
            $records = $this->findMatchingRecords($automation, $trigger);

            foreach ($records as $record) {
                // Check if we already ran this automation for this record today
                if ($this->wasExecutedToday($automation, $record)) {
                    $results['skipped']++;
                    continue;
                }

                // Evaluate and execute
                if ($this->evaluator->evaluate($automation, $record)) {
                    $results['executed']++;
                } else {
                    $results['skipped']++;
                }
            }
        }

        return $results;
    }

    /**
     * Find records that match the trigger criteria.
     */
    protected function findMatchingRecords(Automation $automation, $trigger): \Illuminate\Support\Collection
    {
        $fieldName = $trigger->field_name;
        $offsetDays = $trigger->offset_days ?? 0;

        // Calculate target date
        $targetDate = Carbon::today()->addDays($offsetDays)->format('Y-m-d');

        // Query records in the collection
        $records = Record::where('collection_id', $automation->collection_id)
            ->whereNotNull('data->' . $fieldName)
            ->get()
            ->filter(function ($record) use ($fieldName, $targetDate) {
                $dateValue = $record->data[$fieldName] ?? null;
                
                if (!$dateValue) {
                    return false;
                }

                // Normalize date to Y-m-d format
                try {
                    $recordDate = Carbon::parse($dateValue)->format('Y-m-d');
                    return $recordDate === $targetDate;
                } catch (\Exception $e) {
                    return false;
                }
            });

        return $records;
    }

    /**
     * Check if automation was already executed for this record today.
     */
    protected function wasExecutedToday(Automation $automation, Record $record): bool
    {
        return $automation->logs()
            ->where('record_id', $record->id)
            ->where('status', 'success')
            ->whereDate('executed_at', Carbon::today())
            ->exists();
    }

    /**
     * Process event-based automation (called when record is created/updated).
     */
    public function processEvent(string $eventType, Record $record, ?array $changes = null): void
    {
        // Find automations for this collection with matching event trigger
        $automations = Automation::with(['triggers', 'conditions', 'actions'])
            ->active()
            ->forCollection($record->collection_id)
            ->whereHas('triggers', function ($query) use ($eventType) {
                $query->where('type', $eventType);
            })
            ->get();

        foreach ($automations as $automation) {
            // Check if trigger conditions match
            if ($this->eventTriggerMatches($automation, $eventType, $record, $changes)) {
                $this->evaluator->evaluate($automation, $record);
            }
        }
    }

    /**
     * Check if event trigger conditions match.
     */
    protected function eventTriggerMatches(Automation $automation, string $eventType, Record $record, ?array $changes): bool
    {
        $trigger = $automation->triggers->where('type', $eventType)->first();

        if (!$trigger) {
            return false;
        }

        // For record_updated, check if specific field changed
        if ($eventType === 'record_updated' && $trigger->field_name) {
            if (!$changes || !isset($changes[$trigger->field_name])) {
                return false;
            }
        }

        // For status_changed, check if status field changed
        if ($eventType === 'status_changed') {
            if (!$changes || !isset($changes['status'])) {
                return false;
            }
        }

        return true;
    }
}
