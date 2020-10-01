<?php

namespace App\Http\Controllers;

use App\Basket;
use App\Event;
use Illuminate\Http\Request;


class BasketController extends Controller
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

            $baskets = Basket::leftjoin('events','events.id', '=', 'baskets.event_id')
            ->leftjoin('tickets','tickets.id', '=', 'baskets.ticket_id')
            ->leftjoin('promo_codes','promo_codes.id', '=','baskets.promocode_id' )
            //leftjoin uses all columns in left table, even if not match in right table
            //->where('events.id','=', $id)
            ->select('baskets.*', 'events.event_date','tickets.title as ticket_title','promo_codes.code as promocode' )
            ->orderby('updated_at','desc')
            ->get();

            return response()->json([
                'baskets'    => $baskets,
            ], 200);

        }

        return view('admin.basket');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //create and update are combined in store function below
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'event_id'         => 'numeric|required|min:1',
            'nr_tickets'         => 'numeric|required|min:0',

        ]);

        $event = Event::find($request->event_id);
        //logger()->channel('info')->info('event:'.$event);

        //set basket to empty
        $basket = "";

        //check if we have a basket id in the session, if not the the session has expired and this is a new session.
        $basket_id = session('basket_id');

        //we have a basket id, let's check if basket is still there, if not it has been removed by refreshbaskets, because it was expired
        if ($basket_id){

            $basket = Basket::find($basket_id);
        }

        $available_tickets = $event->getAvailableTickets();
        //now we know the availability, let's see if we have enough
        if ($available_tickets < $request->nr_tickets){

            if ($available_tickets==0) $message='Another client is currently holding the last tickets. Please try again in 10 minutes.';
            else $message='not enough tickets available, only '.$available_tickets.' tickets available';

            return response()->json([
                'errors'       => [
                    'error'=> $message
                ]
            ], 422);
        }

        //at this point we still have a basket or otherwise still enough tickets, so let's create or update the basket
        $basket = Basket::updateOrCreate(

            [ 'id'                    => $basket_id
            ],

            [
                'event_id'                 => request('event_id'),
                'ticket_id'           => request('ticket_id'),
                'promocode_id'           => request('promocode_id'),
                'promocode_code'           => request('promocode_code'),
                'nr_tickets'           => request('nr_tickets'),
                'name'           => request('name'),
                'email'           => request('email'),
                'phone'           => request('phone'),
                'country_code'           => request('country_code'),
                'dial_code'           => request('dial_code'),
                'lang'           => request('lang'),
                'amount_paid'           => request('paying_now'),
                'total_amount'           => request('total_amount'),
                'total_discount'           => request('total_discount'),
                'guestlist_comments'           => request('guestlist_comments'),
                'admin_comments'           => request('admin_comments'),
                'ticket_nr'           => request('ticket_nr'),
                'extras'        => request('extras'),
                'status'        => '',
            ]);

        //set (or overwrite) basket id in session
        session(['basket_id' => $basket['id']]);
        logger()->channel('info')->info('basket updated');

        return response()->json([
            'message'       => 'success'
        ], 200);
    }


    public function checkBasketComplete(Request $request)
    {
        //logger()->channel('info')->info($request);

        $this->validate($request, [
            'event_id'    => 'numeric|required|min:1',
            'nr_tickets'  => 'numeric|required|min:1',
            'ticket_id'    => 'numeric|required|min:1',
            'name'        => 'required|min:3',
            'email'     => 'required|email',
            'phone'     => 'required',
            'total_amount'=> 'required',

        ],
        [
            'event_id.min' => 'You have not chosen an event',
            'nr_tickets.min' => 'You have not selected any tickets',
            'ticket_id.min' => 'You have not selected a ticket type',
            'email.required' => 'We need to know your e-mail address to send the tickets',
        ]

    );

        $event = Event::find($request->event_id);
        //logger()->channel('info')->info('event:'.$event);

        if(!$event){
            return response()->json([
                'errors'       => [
                    'error'=> 'no event selected'
                ]
            ], 422);
        }
        //set basket to empty
        $basket = "";

        //check if we have a basket id in the session, if not the the session has expired and this is a new session.
        $basket_id = session('basket_id');

        //we have a basket id, let's check if basket is still there, if not it has been removed by refreshbaskets, because it was expired
        if ($basket_id){

            $basket = Basket::find($basket_id);
        }

        $available_tickets = $event->getAvailableTickets();

        //now we know the availability, let's see if we have enough
        if ($available_tickets < $request->nr_tickets){

           $message='You have waited too long, not enough tickets available now, someone else is holding tickets, please wait 10 minutes';

            return response()->json([
                'errors'       => [
                    'error'=> $message
                ]
            ], 422);
        }

        //at this point we still have a basket or otherwise still enough tickets, so let's create or update the basket

        $basket = Basket::updateOrCreate(

            [ 'id'                    => $basket_id
        ],

            [
            'event_id'                 => request('event_id'),
            'ticket_id'           => request('ticket_id'),
            'promocode_id'           => request('promocode_id'),
            'promocode_code'           => request('promocode_code'),
            'nr_tickets'           => request('nr_tickets'),
            'name'           => request('name'),
            'email'           => request('email'),
            'phone'           => request('phone'),
            'country_code'           => request('country_code'),
            'dial_code'           => request('dial_code'),
            'lang'           => request('lang'),
            'amount_paid'           => request('paying_now'),
            'total_amount'           => request('total_amount'),
            'total_discount'           => request('total_discount'),
            'guestlist_comments'           => request('guestlist_comments'),
            'admin_comments'           => request('admin_comments'),
            'ticket_nr'           => request('ticket_nr'),
            'extras'        => request('extras'),
            'status'        => 'psp',
        ]);



        // create a ticket number
        $basket->ticket_nr = "AJC-" . date('dmy',strtotime($event->event_date)) ."-". date("dm") ."-". $basket->id;
        $basket->touch();//to update the timestamp updated_at even if there were not changes
        $basket->save();

        //set (or overwrite) basket id in session
        session(['basket_id' => $basket['id']]);
        //place ticket number in session. Used on front to show on confirmation screen
   //     session(['ticket_nr' => $request->reference]);


        logger()->channel('info')->info('basket complete and updated');

        //return the ticketnumber so we can send it to psp
        return response()->json([
            'ticket_nr'       => $basket->ticket_nr
        ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function show(Basket $basket)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function edit(Basket $basket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Basket $basket)
    {
        $message="";
        if($basket){

            $basket->delete();
            $message='Basket (last updated: "'.$basket->updated_at.'") deleted ';
        }

        logger()->channel('info')->info('Basket: "'.$basket->updated_at.'" deleted by '.auth()->user()->name);
      //  session()->flash('alert-success', 'Basket (last updated: '.$basket->updated_at.') deleted');
        // return redirect( route('events.index') );
        return response()->json([
            'message'       => $message
        ], 200);
    }

    public function deleteSessionBasket()
    {
        $message="no session basket found, nothing deleted";

            //check if basket is in session id
            $basket_id = session('basket_id');
            if($basket_id){
                $basket = Basket::find($basket_id);
                if ($basket){
                    $basket->delete();
                    $message='Found session basket: (last updated: "'.$basket->updated_at.'") deleted ';
                }
            }

       // logger()->channel('info')->info('Reservation: "'.$basket->updated_at.'" deleted by '.auth()->user()->name);
      //  session()->flash('alert-success', 'Basket (last updated: '.$basket->updated_at.') deleted');
        // return redirect( route('events.index') );
        return response()->json([
            'message'       => $message
        ], 200);
    }

    public function extendBasketLifetime()
    {
       //set basket to empty
        $basket = "";

        //check if we have a basket id in the session, if not the the session has expired and this is a new session.
        $basket_id = session('basket_id');

        //we have a basket id, let's check if basket is still there, if not it has been removed by refreshbaskets, because it was expired
        if ($basket_id){

            $basket = Basket::find($basket_id);
        }
        if ($basket){
            $basket->extendLifetime();

        }
        if (!$basket){
            logger()->channel('info')->info('extending basket lifetime failed, no basket found');
        }
    }

    public function refreshBaskets()
    {
        logger()->channel('info')->info('refreshing baskets');
        //possible update: from baskets older than 10 minutes only remove tickets and delete baskets after approx 30 minutes.

        //go through all baskets and delete the ones that have not been updated for longer than the threshold
        $date = new \DateTime;
        $minutes = config('custom.basket_lifetime');
        $date->modify(- $minutes.' minutes');
        $deadline = $date->format('Y-m-d H:i:s');

        //get all expired baskets
        $baskets = Basket::where('updated_at', '<' , $deadline)->get();

        //check all expired baskets for open statusses, which can not be deleted
        //if open baskets are found, the status will be set to admin and a warning will be emailed and logged
        foreach ($baskets as $basket) {
           // logger()->channel('info')->info('expired basket found with id: ' . $basket->id);

            if ($basket->status == "completed" ||
                $basket->status == "initialized" ||
                $basket->status == "uncleared" ||
                $basket->status == "psp" ||
                $basket->status == "admincheck"){

                if ($basket->status != "admincheck"){
                    //update status and warn admin
                    logger()->channel('info')->info('ADMIN WARNING: check basket with id: ' . $basket->id . " and ticket nr: ". $basket->ticket_nr . " Status was: ".$basket->status);
                    $basket->updateStatus("admincheck");
                    //TODO send admin email

                }
            }
            else{
                $basket->delete();
            }
        }
    }

    public function getSessionBasket(){

        $basket_id = session('basket_id');

        if($basket_id){

            $basket = Basket::find($basket_id);

            if ($basket){
                return response()->json([
                    'basket'    => $basket,
                ], 200);
            }
        }
    }




}
