<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketGroup extends Model
{
    protected $guarded = [ 'id'];

    public function tickets()
    {
        //return all tickets connected to a ticketgroup, sorted by order
        return $this->belongsToMany('App\Ticket')->orderBy('order');
    }

    public function events()
    {
        //return all events connected to a ticketgroup, sorted by date
        return $this->hasMany('App\Event')->orderBy('event_date');
    }
}
