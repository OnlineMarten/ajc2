<?php

namespace App\Http\Controllers;

use App\Basket;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $this->validate($request, [
            'event_id'         => 'required',
            'nr_tickets'         => 'required|min:1',

        ]);




        //check if we have a basket id
        $basket_id = session('basket_id');
        $basket = "";

        //we have a basket id, let 's check if basket is still there
        if($basket_id){

            $basket = Basket::find($basket_id);

        }

        if (!$basket){
            //no basket, let's check availability

            //check availability here
            $enough_tickets = true;

            if (!$enough_tickets){
                return response()->json([
                    'errors'       => [
                        'error'=>'not enough tickets available'
                    ]
                ], 422);
            }

        }



        //at this point we still have a basket or otherwise still enough tickets, so let's create or update the basket

        //in case we are updating an exisitng reservation we have to check for already made payments
        $total_paid = request('amount_paid')+request('paying_now');


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
            'amount_paid'           => $total_paid,
            'total_amount'           => request('total_amount'),
            'total_discount'           => request('total_discount'),
            'guestlist_comments'           => request('guestlist_comments'),
            'admin_comments'           => request('admin_comments'),
            'ticket_nr'           => request('ticket_nr'),
            'extras'        => request('extras'),
        ]);

        //set basket id in session
        session(['basket_id' => $basket['id']]);

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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Basket  $basket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Basket $basket)
    {
        //
    }

    public function refreshBaskets()
    {
        //possible update: from baskets older than 10 minutes only remove tickets and delete baskets after approx 30 minutes.

        //go through all baskets and delete the ones that are older than the threshold
        $date = new \DateTime;
        $minutes = config('custom.basket_lifetime');
        $date->modify(- $minutes.' minutes');
        $deadline = $date->format('Y-m-d H:i:s');

        Basket::where('updated_at', '<' , $deadline)->delete();
        return response()->json([
            'message' => 'deadline'.$deadline
        ], 200);
    }
}
