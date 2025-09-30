<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = ['name','description'];

    public function issues() {
        return $this->hasMany(Issue::class);
    }
}
