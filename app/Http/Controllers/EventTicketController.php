<?php

namespace App\Http\Controllers;

use App\Event;
use App\Ticket;
use Illuminate\Http\Request;

class EventTicketController extends Controller
{
    public function allTicketsConnectedToEvent($event_id)
    {
        //get all tickets  connected to a specific event

        $event = Event::findOrFail($event_id);

        $checkedtickets = $event->tickets->pluck('id');

        return response()->json([
            'checkedtickets' => $checkedtickets,
        ], 200);
    }
}
