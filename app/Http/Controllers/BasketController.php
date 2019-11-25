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

            $baskets = Basket::join('events','events.id', '=', 'baskets.event_id')
            ->join('tickets','tickets.id', '=', 'baskets.ticket_id')
            ->leftjoin('promo_codes','promo_codes.id', '=','baskets.promocode_id' )
            //leftjoin uses all columns in left table, even if not match in right table

      //      ->where('events.id','=', $id)

            ->select('baskets.*', 'events.event_date','tickets.title as ticket_title','promo_codes.code as promocode' )
                    ->orderby('updated_at','desc')
            ->get();



/*
            $sales = Sale::

            orderBy("created_at")
            ->get();
*/
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
            'event_id'         => 'required',
            'nr_tickets'         => 'required|min:1',

        ]);
        $event = Event::find($request->event_id);
        logger()->channel('info')->info('event:'.$event);


        //set basket to empty
        $basket = "";

        //check if we have a basket id in the session, if not the the session has expired and this is a new session.
        $basket_id = session('basket_id');


        //we have a basket id, let's check if basket is still there, if not it has been removed by refreshbaskets, because it was expired
        if($basket_id){

            $basket = Basket::find($basket_id);

        }
        if ($basket) {//check availability excluding current basket
            $basket->nr_tickets=0;
            $basket->save();
        }
        $available_tickets = $event->getAvailableTickets();

        //now we know the availability, let's see if we have enough
        if ($available_tickets < $request->nr_tickets){

            return response()->json([
                'errors'       => [
                    'error'=>'not enough tickets available, only '.$available_tickets.' tickets available'
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
        ]);

        //set (or overwrite) basket id in session
        session(['basket_id' => $basket['id']]);
        logger()->channel('info')->info('basket updated');

        return response()->json([
            'message'       => 'success'
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
       /*
        $this->validate($request, [
            'event_id'         => 'required',
            'ticket_id'         => 'required',
            'nr_tickets'         => 'required|min:1',

        ]);

        $basket = Basket::findOrFail($id);

        $basket->event_id = request('event_id');

        if (!$basket->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }

        return response()->json([
            'message' => 'success'
        ], 200);
        */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Basket $basket)
    {
        $basket->delete();
        logger()->channel('info')->info('Reservation: "'.$basket->updated_at.'" deleted by '.auth()->user()->name);
        session()->flash('alert-success', 'Basket (last updated: '.$basket->updated_at.') deleted');
        // return redirect( route('events.index') );
        return response()->json([
            'message'       => 'Basket (last updated: "'.$basket->updated_at.'") deleted '
        ], 200);
    }

    public function refreshBaskets()
    {
        //possible update: from baskets older than 10 minutes only remove tickets and delete baskets after approx 30 minutes.

        //go through all baskets and delete the ones that have not been updated for longer than the threshold
        $date = new \DateTime;
        $minutes = config('custom.basket_lifetime');
        $date->modify(- $minutes.' minutes');
        $deadline = $date->format('Y-m-d H:i:s');

        Basket::where('updated_at', '<' , $deadline)->delete();
        /*
        return response()->json([
            'message' => 'deadline'.$deadline
        ], 200);
        */
    }


}
