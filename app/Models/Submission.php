<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['title','abstract','user_id','conference_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }

    public function authors()
    {
        return $this->hasMany(SubmissionAuthor::class);
    }

}
