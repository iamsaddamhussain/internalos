<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationCondition extends Model
{
    protected $fillable = [
        'automation_id',
        'field',
        'operator',
        'value',
        'condition_group',
    ];

    /**
     * Get the automation that owns the condition.
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * Evaluate the condition against a record.
     */
    public function evaluate($fieldValue): bool
    {
        return match ($this->operator) {
            '=' => $fieldValue == $this->value,
            '!=' => $fieldValue != $this->value,
            '>' => $fieldValue > $this->value,
            '<' => $fieldValue < $this->value,
            '>=' => $fieldValue >= $this->value,
            '<=' => $fieldValue <= $this->value,
            'contains' => str_contains(strtolower($fieldValue), strtolower($this->value)),
            'not_contains' => !str_contains(strtolower($fieldValue), strtolower($this->value)),
            default => false,
        };
    }
}
