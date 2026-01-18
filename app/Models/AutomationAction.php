<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationAction extends Model
{
    protected $fillable = [
        'automation_id',
        'type',
        'target',
        'channel',
        'config',
        'order',
    ];

    protected $casts = [
        'config' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the automation that owns the action.
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * Check if this is a notification action.
     */
    public function isNotificationAction(): bool
    {
        return $this->type === 'notify';
    }

    /**
     * Check if this is an email action.
     */
    public function isEmailAction(): bool
    {
        return $this->type === 'email';
    }
}
