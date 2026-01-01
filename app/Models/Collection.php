<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['workspace_id', 'name', 'slug', 'icon', 'description', 'schema'];

    protected $casts = [
        'schema' => 'array',
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
