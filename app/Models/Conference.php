<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conference extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'abstract_deadline',
        'fullpaper_deadline',
        'review_deadline',
        'camera_ready_deadline',
        'fee',
        'rules',
        'accronym',
        'organiser',
        'location',
    ];

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
}
