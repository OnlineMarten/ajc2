<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    protected $guarded = ['id'];

    use SoftDeletes;

    public function extras()
    {
        //return all extras connected to an sale
        return $this->belongsToMany('App\Extra')->withPivot('nr')->withTimestamps();
    }
}
