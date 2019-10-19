<?php

namespace App\Http\Controllers;

use App\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //used from router to display page and from an axios call to get the (initial) data

        if($request->wantsJson())  {


            $events = Event::
            orderBy("event_date")
            ->get();

                return response()->json([
                    'events'    => $events,
                ], 200);
            }

            return view('admin.event');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate(request(), [

            'title' => 'required|min:3',
            'event_date' => 'required',//|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|numeric|min:0'
        ]);

        //place event dates in array
        $event_dates = explode (",", $request->event_date);

        foreach ($event_dates as &$event_date) {

            $event = new Event;

            if (!$request->tickets_reserved) $request->tickets_reserved=0;

            $event->title = $request->title;
            $event->description = $request->description;
            $event->event_date = $event_date;
            $event->start_time = $request->start_time;
            $event->end_time = $request->end_time;
            $event->min_per_sale = $request->min_per_sale;
            $event->max_per_sale = $request->max_per_sale;
            $event->capacity = $request->capacity;
            $event->tickets_reserved = $request->tickets_reserved;
            $event->active = $request->active;

            $event->save();
        }

        //attach the selected upgrades to the event
     //   $event->tickets()->sync($request->tickets);
     //   $event->upgrades()->sync($request->upgrades);

        logger()->channel('info')->info('Event: "'.$event->title.', date: '. $event->event_date.'" created by '.auth()->user()->name);

        //session()->flash('alert-success', 'Event added');


        return response()->json([
            'message'       => 'New Event "'.request("title"). '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate(request(), [

            'title' => 'required|min:3',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'capacity' => 'required|numeric|min:0'
        ]);

        $event = Event::findOrFail($id);

        $event->title = request('title');
        $event->description = request('description');
        $event->event_date = request('event_date');
        $event->start_time = request('start_time');
        $event->end_time = request('end_time');
        $event->min_per_sale = request('min_per_sale');
        $event->max_per_sale = request('max_per_sale');
        $event->capacity = request('capacity');
        $event->tickets_reserved = request('tickets_reserved');
        $event->active = request('active');

        if (!$event->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }

        logger()->channel('info')->info('event "'.$event->title.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Event "'. $event->title .'" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //check if we can safely delete (no tickets should have been sold)
        if ($event->tickets_sold > 0){
            return response()->json([
                'message'       => 'Can not delete the event of "'.\Carbon\Carbon::parse($event->event_date)->formatLocalized('%a %d %b %Y'). '" , because there are already tickets sold!'
            ], 200);
           // session()->flash('alert-danger', 'Can not delete the event of '. \Carbon\Carbon::parse($event->event_date)->formatLocalized('%a %d %b %Y').', because there are already tickets sold!');
        }
        else{
          //  $event->tickets()->detach();//remove all connected tickets (and delete from pivot table)
         //   $event->upgrades()->detach();//remove all connected upgrades (and delete from pivot table)

            $event->delete();
            logger()->channel('info')->info('Event: "'.$event->title.', date: '. $event->event_date.'" deleted by '.auth()->user()->name);
            session()->flash('alert-success', 'Event deleted');
        }
       // return redirect( route('events.index') );
       return response()->json([
        'message'       => 'Event: "'.$event->title.', date: '. $event->event_date.'" deleted '
    ], 200);
    }
}
