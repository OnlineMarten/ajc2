<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [ 'id'];

    public function events()
    {
        return $this->belongsToMany('App\Event')->orderBy('event_date')->withTimestamps();
    }
}
