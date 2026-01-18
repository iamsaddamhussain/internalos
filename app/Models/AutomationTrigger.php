<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationTrigger extends Model
{
    protected $fillable = [
        'automation_id',
        'type',
        'field_name',
        'offset_days',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'offset_days' => 'integer',
    ];

    /**
     * Get the automation that owns the trigger.
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * Check if this is a date-based trigger.
     */
    public function isDateTrigger(): bool
    {
        return $this->type === 'date_reached';
    }

    /**
     * Check if this is an event-based trigger.
     */
    public function isEventTrigger(): bool
    {
        return in_array($this->type, ['record_created', 'record_updated', 'comment_added', 'status_changed']);
    }
}
