<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['workspace_id', 'name', 'slug', 'icon', 'description', 'schema', 'enable_search', 'enable_export', 'enable_import', 'per_page'];

    protected $casts = [
        'schema' => 'array',
        'enable_search' => 'boolean',
        'enable_export' => 'boolean',
        'enable_import' => 'boolean',
        'per_page' => 'integer',
    ];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function records()
    {
        return $this->hasMany(Record::class);
    }
}
