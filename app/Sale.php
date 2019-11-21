<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $guarded = ['id'];

    public function extras()
    {
        //return all extras connected to an sale
        return $this->belongsToMany('App\Extra')->withPivot('nr')->withTimestamps();
    }
}
