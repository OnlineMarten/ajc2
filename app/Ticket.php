<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = ['id'];

    public function ticketgroups()
    {
        return $this->belongsToMany('App\TicketGroup')->orderBy('order')->withTimestamps();
    }
}
