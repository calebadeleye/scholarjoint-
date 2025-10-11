<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'conference_id',
        'name',
    ];

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }
}
