<?php

namespace App\Http\Controllers;

use App\Event;
use App\TicketGroup;
use App\Extra;
use App\Sale;
use App\Log;

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
            withCount('categories')
            ->orderBy("event_date")
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
                'ticket_group_id' => 'required',
            ],
            [
                'ticket_group_id.required' => 'Please select a ticket group'
            ]);
        }
        else {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'ticket_group_id' => 'required',
            ],
            [
                'ticket_group_id.required' => 'Please select a ticket group'
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
                $event->ticket_group_id = $request->ticket_group_id;
                if($request->min_per_sale) {$event->min_per_sale = $request->min_per_sale;}else $event->min_per_sale = 1;
                if($request->max_per_sale) {$event->max_per_sale = $request->max_per_sale;}else $event->max_per_sale =$event->capacity;
                if($request->tickets_reserved) {$event->tickets_reserved = $request->tickets_reserved;}else $event->tickets_reserved = 0;
                $event->active = $request->active;
                $event->save();

             //   $event->ticketgroups()->sync($request->checkedTicketGroup);//attach tickets to event
                $event->categories()->sync($request->checkedCategories);//attach categories to event

               // $event_date = date('d-m-Y', strtotime($corrected_date));//convert to readable for log
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
    public function show($id)
    {
        //EVENT
        $event = Event::find($id);//get event

        //TICKETGROUP
        $ticketgroup = $event->ticketgroup()->first();

        //TICKETS
        $tickets = TicketGroup::find($event->ticket_group_id)->tickets()->get();//get event tickets


        //CATEGORIES
        $categories = $event->categories()->get();//get event categories

        //EXTRAS
        foreach ($categories as $category){
                $category['extras'] = $category->extras()->get();//attach all extras per category to the category
        }

        return response()->json([ 'event' => [

                'event'    => $event,
                'ticketgroup' =>$ticketgroup,
                'tickets'    => $tickets,
                'categories'  => $categories,
        ]
            ], 200);
    }

/*dit werkt, maar levert 1 table op, wat betekent maar 1 row per table
        $event = Event::join('ticket_groups','ticket_groups.id', '=', 'ticket_group_id')
            ->join('ticket_ticket_group','ticket_ticket_group.ticket_id', '=', 'ticket_ticket_group.ticket_group_id')
            ->join('tickets','tickets.id', '=','ticket_ticket_group.ticket_id' )
            ->join('category_event', 'category_event.event_id', '=', 'events.id')
            ->join('category_extra' , 'category_extra.category_id', '=', 'category_event.category_id')
            ->join('extras', 'extras.id', '=', 'category_extra.extra_id')
            ->where('events.id','=', $id)
            ->select('events.*', 'ticket_groups.*','tickets.*', 'extras.*')
            ->first();
*/


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
                'ticket_group_id' => 'required',
            ],
            [
                'ticket_group_id.required' => 'Please select a ticket group'
            ]);
        }
        else {
            $this->validate(request(), [

                'title' => 'required|min:3',
                'event_date' => 'required',//|date',
                'start_time' => 'required',
                'end_time' => 'required',
                'capacity' => 'required|numeric|min:1',
                'ticket_group_id' => 'required',
            ],
            [
                'ticket_group_id.required' => 'Please a ticket group'
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
        $event->ticket_group_id = request('ticket_group_id');
        $event->tickets_reserved = request('tickets_reserved');
        $event->active = request('active');

        if (!$event->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 422);

        }

   //     $event->ticketgroups()->sync($request->checkedTicketGroup);
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
            ], 422);
           // session()->flash('alert-danger', 'Can not delete the event of '. \Carbon\Carbon::parse($event->event_date)->formatLocalized('%a %d %b %Y').', because there are already tickets sold!');
        }
        else{
            $event->categories()->detach();//remove all connected tickets (and delete from pivot table)
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
        //gets all events with a label and color code for available for booking or not
        $events = Event::
        orderBy("event_date")
        ->get();


        $calendarevents = [];
        $e=[];

        foreach ($events as $event) {
            if ($event->active) {



                $hours=date('H', strtotime($event->start_time));
                $minutes=date('i', strtotime($event->start_time));
                $date= strtotime($event->event_date);
                $minutes_close_sale_before_start = config('custom.minutes_close_sale_before_start');//stop verkoop aantal minuten vooraf  aanvang

                $deadline = date('Y-m-d H:i:s', $date+$hours*60*60+$minutes*60-$minutes_close_sale_before_start*60);//create date including start time cruise, calculate end time sales.

                //echo "hours:".$hours; echo "minutes:".$minutes; echo "deadline is:".$deadline;

                $e['date'] = $event->event_date;

                if (strtotime($deadline)< time()) {//cruise al geweest of na de deadline, geen verkoop meer mogelijk
                    $e['title'] = ucwords($event->title)."\nSold Out";
                    $e['backgroundColor'] ="#FA0C00";
                    $e['classNames'] =[ 'closed' ];
                }
                else{
                    $e['id'] = $event->id;
                    //green
                    if ($event->tickets_sold < 8 ) { //TODO threshold (8) via config laten lopen
                        $e['title'] = ucwords($event->title)."\nBook Now";
                        $e['backgroundColor'] ="#70CA2E";
                        $e['classNames'] =[ 'open' ];
                        $e['displayEventTime'] = true;
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
                }//end else


                array_push($calendarevents,$e);

            }

        }



    // Output json for our calendar


                return response()->json([
                    'events'    => $calendarevents,
                ], 200);
    }

    public function openEvents()
    {
        $events = Event::
        orderBy("event_date")
        ->get();


        $calendarevents = [];
        $e=[];

        foreach ($events as $event) {
            if ($event->active) {



                $hours=date('H', strtotime($event->start_time));
                $minutes=date('i', strtotime($event->start_time));
                $date= strtotime($event->event_date);
                //deadline is de starttijd van de cruise, dit is voor admin reserveringen, dus daarom tot starttijd
                $deadline = date('Y-m-d H:i:s', $date+$hours*60*60+$minutes*60);//create date including start time cruise and set this as deadline

                if (strtotime($deadline)> time()) {//cruise nog voor deadline verkoop = aanvangstijd cruise
                    if (!$event->sold_out){//niet uitverkocht
                        $e['id'] =$event->id;
                        $e['date'] = $event->event_date;
                        array_push($calendarevents,$e);
                    }
                }

            }
        }
        // Output json for our calendar
        return response()->json([
            'events'    => $calendarevents,
        ], 200);
    }

    public function allEvents()
    {
        $events = Event::
        orderBy("event_date")
        ->get();


        $allevents = [];
        $e=[];

        foreach ($events as $event) {

            $e['id'] =$event->id;
            $e['date'] = $event->event_date;
            array_push($allevents,$e);
        }

        // Output json for our calendar
        return response()->json([
            'events'    => $allevents,
        ], 200);
    }

    public function allCategoriesConnectedToEvent($event_id)
    {
        //get all tickets  connected to a specific event

        $event = Event::findOrFail($event_id);
        $checkedCategories = $event->categories->pluck('id');

        return response()->json([
            'checkedCategories' => $checkedCategories,
        ], 200);
    }

    public function allTicketsConnectedToEvent($event_id)
    {
        //get all tickets  connected to a specific event

        $event = Event::findOrFail($event_id);
        $tickets = $event->tickets();
        //$checkedTicketGroup = $event->ticketgroups->pluck('id');


        return response()->json([
            'tickets' => $tickets,
        ], 200);
    }

    public function checkEventAvailable($id){
        //EVENT
        $event = Event::find($id);//get event
        if ($event->active && !$event->sold_out){
            return response()->json([
                'available' => true,
            ], 200);
        }
        else{
            return response()->json([
                'available' => false,
            ], 200);
        }
    }

    public function getEvent($event_id){
        $event = Event::find($event_id);//get event
        return $event;
    }


    public function checkAvailability(Request $request){
        $event = Event::find($request->event_id);//get event
        $available = $event->getAvailableTickets($request->sale_id,$request->ignore_reserved);
        return response()->json([
            'available' => $available,
        ], 200);

    }

    public function getEvents($which="all"){
        if($which == "all"){
            //get all events
            $events = Event::
                withCount('categories')
                ->orderBy("event_date")
                ->get();
            return response()->json([
                'events'    => $events,
            ], 200);
        }
        else if($which == "future"){
            //get only events today and in future
            $events = Event::
                withCount('categories')
                ->whereDate("event_date", ">=" , now() )
                ->orderBy("event_date")
                ->get();
            return response()->json([
                'events'    => $events,
            ], 200);

        }
        else{//$which should be an id
            $event = Event::
                withCount('categories')
                ->find($which);
            return response()->json([
                'event'    => $event,
            ], 200);
        }
    }

    public function adjustReservedTickets(Request $request){
        $event = Event::find($request->event_id);

        if (!$event)
            return response()->json([
                'message'    => "event not found, id= ".$request->event_id,
            ], 422);

        if($request->reserved<0)
            return response()->json([
                'message'    => "minimal reservable tickets is zero",
                'reserved'  => 0
            ], 422);

        $max_reservable = $event->getAvailableTickets(NULL,true);//true means ignore reserved tickets

        if ($request->reserved<=$max_reservable){
            $event->tickets_reserved=$request->reserved;
            $event->save();
            $event->updateEventAvailability();
            return response()->json([
                'message'    => "number of reserved tickets adjusted to ".$request->reserved,
                'reserved'  => $request->reserved
            ], 200);
        }
        else{
            $event->tickets_reserved=$max_reservable;
            $event->save();
            $event->updateEventAvailability();
            return response()->json([
                'message'    => "maximal reservable number of tickets is ".$max_reservable,
                'reserved'  => $max_reservable
            ], 422);
        }

    }



}
