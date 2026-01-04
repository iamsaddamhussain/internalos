<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    protected $fillable = ['collection_id', 'data', 'created_by'];

    protected $casts = [
        'data' => 'array',
    ];

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->latest();
    }
}
