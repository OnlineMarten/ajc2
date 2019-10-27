<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [ 'id'];

    public function extras()
    {
        //return all categories connected to an extra, sorted by order
        return $this->belongsToMany('App\Extra')->orderBy('order')->withTimestamps();
    }

    public function events()
    {
        //return all categories connected to an event, sorted by dat
        return $this->belongsToMany('App\Event')->orderBy('event_date')->withTimestamps();
    }
}
