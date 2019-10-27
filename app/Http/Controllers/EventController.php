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

        if( config('custom.minimal_tickets_required') > 0) {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'checkedTickets' => 'required|array|min:1',
            ],
            [
                'checkedTickets.required' => 'Minimal one ticket is needed'
            ]);
        }
        else {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'checkedTickets' => 'required|array|min:1'
            ]);
        }


            //get all dates
            $event_dates = $request->event_date;

            //and loop through them to create identical events
            foreach ($event_dates as $event_date) {

                //first we need to correct the ISO8601 date by changing the timezone from utc (z) to local time, otherwise we end up with the wrong date!
                $corrected_date = new \DateTime( $event_date, new \DateTimeZone( 'UTC' ) );  // as the original datetime string is in Zulu time, set the datetimezone as UTC first
                $corrected_date->setTimezone( new \DateTimeZone( 'Europe/Amsterdam' ) ); // this is what actually sets the timezone

                //create new event
                $event = new Event;

                //fill all aparams except date
                $event->event_date = $corrected_date;
                if (!$request->tickets_reserved) $request->tickets_reserved=0;
                $event->title = $request->title;
                $event->description = $request->description;
                $event->start_time = $request->start_time;
                $event->end_time = $request->end_time;
                $event->capacity = $request->capacity;
                if($request->min_per_sale) {$event->min_per_sale = $request->min_per_sale;}else $event->min_per_sale = 1;
                if($request->max_per_sale) {$event->max_per_sale = $request->max_per_sale;}else $event->max_per_sale =$event->capacity;
                if($request->tickets_reserved) {$event->tickets_reserved = $request->tickets_reserved;}else $event->tickets_reserved = 0;
                $event->active = $request->active;
                $event->save();

                $event->tickets()->sync($request->checkedTickets);//attach tickets to event
                $event->categories()->sync($request->checkedCategories);//attach categories to event

                $event_date = date('d-m-Y', strtotime($corrected_date));//convert to readable for log
                logger()->channel('info')->info('Event: "'.$event->title.', date: '. $event_date.'" created by '.auth()->user()->name);
            }

        //session()->flash('alert-success', 'Event added');
        return response()->json([
            'message'       => 'New Event(s) created'
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
        if( config('custom.minimal_tickets_required') > 0) {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'checkedTickets' => 'required|array|min:1',
            ],
            [
                'checkedTickets.required' => 'Minimal one ticket is needed'
            ]);
        }
        else {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'checkedTickets' => 'required|array|min:1'
            ]);
        }

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

        $event->tickets()->sync($request->checkedTickets);
        $event->categories()->sync($request->checkedCategories);

        logger()->channel('info')->info('event "'.$event->title.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Event "'. $event->title . $event->event_date . '" updated'
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
         //   $event->upgrades()->detach();//remove all connected upgrades (and delete from pivot table)

            $event->tickets()->detach();//remove all connected tickets (and delete from pivot table)
            $event->delete();
            logger()->channel('info')->info('Event: "'.$event->title.', date: '. $event->event_date.'" deleted by '.auth()->user()->name);
            session()->flash('alert-success', 'Event deleted');
        }
       // return redirect( route('events.index') );
       return response()->json([
        'message'       => 'Event: "'.$event->title.', date: '. $event->event_date.'" deleted '
    ], 200);
    }


    public function calendarEvents()
    {
        $events = Event::
        orderBy("event_date")
        ->get();


        $calendarevents = [];
        $e=[];

        foreach ($events as $event) {
            if ($event->active) {

                $e['date'] = $event->event_date;
                $e['id'] = $event->id;
                //green
                if ($event->tickets_sold < 8 ) {
                    $e['title'] = ucwords($event->title)."\nBook Now";
                    $e['backgroundColor'] ="#70CA2E";
                    $e['classNames'] =[ 'open' ];
                }
                //yellow
                if ($event->tickets_sold >= 8 ) {
                    $e['title'] = ucwords($event->title)."\nBook Now";
                    $e['backgroundColor'] ="#F19B12";
                    $e['classNames'] =[ 'open' ];

                }
                //red
                if ($event->sold_out) {
                    $e['title'] = ucwords($event->title)."\nSold Out";
                    $e['backgroundColor'] ="#FA0C00";
                    $e['classNames'] =[ 'closed' ];

                }


                array_push($calendarevents,$e);

            }

        }



    // Output json for our calendar


                return response()->json([
                    'events'    => $calendarevents,
                ], 200);
    }

    public function allCategoriesConnectedToEvent($event_id)
    {
        //get all tickets  connected to a specific event

        $event = Event::findOrFail($event_id);
        $checkedcategories = $event->categories->pluck('id');

        return response()->json([
            'checkedcategories' => $checkedcategories,
        ], 200);
    }

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
