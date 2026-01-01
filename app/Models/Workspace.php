<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'owner_id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workspace')->withPivot('role_id')->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }
}
