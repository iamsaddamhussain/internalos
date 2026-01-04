<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'record_id',
        'title',
        'description',
        'status',
        'created_by',
        'signed_off_by',
        'signed_off_at',
    ];

    protected $casts = [
        'signed_off_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function signedOffBy()
    {
        return $this->belongsTo(User::class, 'signed_off_by');
    }

    public function signOff($userId)
    {
        $this->update([
            'status' => 'done',
            'signed_off_by' => $userId,
            'signed_off_at' => now(),
        ]);
    }

    public function isSignedOff()
    {
        return $this->status === 'done' && $this->signed_off_by !== null;
    }
}
