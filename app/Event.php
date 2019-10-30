<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    public function ticketgroup()
    {
        //return the tickegtgroup connected to the event
        return $this->belongsTo('App\TicketGroup','ticket_group_id');//foreign key added because laravel proably expects it to be ticketgroup_id
    }


    public function categories()
    {
        //return all categories connected to an event, sorted by order
        return $this->belongsToMany('App\Category');
    }
/*
    public function tickets()
    {
        //return all tickets connected to an event sorted by order
        return $this->hasManyThrough('App\Ticket', 'App\TicketGroup');
    }
*/
}
