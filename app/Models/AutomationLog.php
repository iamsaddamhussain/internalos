<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'automation_id',
        'record_id',
        'status',
        'message',
        'context',
        'executed_at',
    ];

    protected $casts = [
        'context' => 'array',
        'executed_at' => 'datetime',
    ];

    /**
     * Get the automation that owns the log.
     */
    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * Get the record associated with the log.
     */
    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    /**
     * Create a success log.
     */
    public static function logSuccess(int $automationId, ?int $recordId, string $message, array $context = []): self
    {
        return self::create([
            'automation_id' => $automationId,
            'record_id' => $recordId,
            'status' => 'success',
            'message' => $message,
            'context' => $context,
            'executed_at' => now(),
        ]);
    }

    /**
     * Create a failed log.
     */
    public static function logFailed(int $automationId, ?int $recordId, string $message, array $context = []): self
    {
        return self::create([
            'automation_id' => $automationId,
            'record_id' => $recordId,
            'status' => 'failed',
            'message' => $message,
            'context' => $context,
            'executed_at' => now(),
        ]);
    }

    /**
     * Create a skipped log.
     */
    public static function logSkipped(int $automationId, ?int $recordId, string $message, array $context = []): self
    {
        return self::create([
            'automation_id' => $automationId,
            'record_id' => $recordId,
            'status' => 'skipped',
            'message' => $message,
            'context' => $context,
            'executed_at' => now(),
        ]);
    }

    /**
     * Scope successful logs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
