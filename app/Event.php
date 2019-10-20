<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public function tickets()
    {
        //return all tickets connected to a cruise, sorted by order
        return $this->belongsToMany('App\Ticket')->orderBy('order')->withTimestamps();
    }
}
