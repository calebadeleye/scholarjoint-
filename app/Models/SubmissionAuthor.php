<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionAuthor extends Model
{

    protected $fillable = [
        'submission_id',
        'name',
        'email',
        'institution',
        'department',
        'is_corresponding',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}
