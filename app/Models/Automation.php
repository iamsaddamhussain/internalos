<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    protected $fillable = [
        'workspace_id',
        'collection_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'workspace_id' => 'string', // UUID
    ];

    /**
     * Get the workspace that owns the automation.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the collection that owns the automation.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the triggers for the automation.
     */
    public function triggers(): HasMany
    {
        return $this->hasMany(AutomationTrigger::class);
    }

    /**
     * Get the conditions for the automation.
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(AutomationCondition::class);
    }

    /**
     * Get the actions for the automation.
     */
    public function actions(): HasMany
    {
        return $this->hasMany(AutomationAction::class)->orderBy('order');
    }

    /**
     * Get the logs for the automation.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class);
    }

    /**
     * Scope active automations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific collection.
     */
    public function scopeForCollection($query, $collectionId)
    {
        return $query->where('collection_id', $collectionId);
    }

    /**
     * Scope for a specific workspace.
     */
    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }
}
