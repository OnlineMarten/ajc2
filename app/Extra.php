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

    public function events()
    {
        return $this->belongsToMany('App\Event')->orderBy('event_date')->withTimestamps();
    }
}
