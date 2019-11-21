<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    protected $guarded = [ 'id'];

    public function categories()
    {
        //return all tickets connected to a cruise, sorted by order
        return $this->belongsToMany('App\Category')->orderBy('order')->withTimestamps();
    }

    public function sales()
    {
        //return all categories connected to an event, sorted by order
        return $this->belongsToMany('App\Sale')->withPivot('nr')->withTimestamps();
    }

    /*
    public function events()
    {
        return $this->hasManyThrough
    }
    */
}
