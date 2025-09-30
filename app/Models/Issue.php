<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = ['journal_id','volume','issue_number','publication_date'];

    public function journal() {
        return $this->belongsTo(Journal::class);
    }
}
