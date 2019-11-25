<?php

namespace App\Http\Controllers;

use App\Sale;
use App\Basket;
use Illuminate\Http\Request;

class SaleController extends Controller
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

            $sales = Sale::join('events','events.id', '=', 'sales.event_id')
            ->join('tickets','tickets.id', '=', 'sales.ticket_id')
            ->leftjoin('promo_codes','promo_codes.id', '=','sales.promocode_id' )
            //leftjoin uses all columns in left table, even if not match in right table

      //      ->where('events.id','=', $id)

            ->select('sales.*', 'events.event_date','tickets.title as ticket_title' )
                    ->orderby('created_at','desc')
            ->get();



/*
            $sales = Sale::

            orderBy("created_at")
            ->get();
*/
                return response()->json([
                    'sales'    => $sales,
                ], 200);
            }

            return view('admin.sale');

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
            'event_id'    => 'required',
            'ticket_id'    => 'required',
            'nr_tickets'  => 'required|min:1',
            'name'        => 'required|min:3',
            'total_amount'=> 'required',

        ]);


        //check if we have a basket id
        $basket_id = session('basket_id');
        $basket = "";


        //we have a basket id, let 's check if basket is still there
        if ($basket_id){
            $basket = Basket::find($basket_id);
        }

        if (!$basket){
            //no basket, return with error, should never happen as basket was successfully created less than a second ago
            //send email
                return response()->json([
                    'errors'       => [
                        'error'=>'can not add sale, no basket!!'
                    ]
                ], 422);
            }


        $sale = Sale::create([
            'event_id'                 => $basket->event_id,
            'ticket_id'           => $basket->ticket_id,
            'promocode_id'           => $basket->promocode_id,
            'nr_tickets'           => $basket->nr_tickets,
            'name'           => $basket->name,
            'email'           => $basket->email,
            'phone'           => $basket->phone,
            'country_code'           => $basket->country_code,
            'dial_code'           => $basket->dial_code,
            'lang'           => $basket->lang,
            'amount_paid'           => $basket->amount_paid,
            'total_amount'           => $basket->total_amount,
            'total_discount'           => $basket->total_discount,
            'guestlist_comments'           => $basket->guestlist_comments,
            'admin_comments'           => $basket->admin_comments,
            'ticket_nr'           => $basket->ticket_nr,
           // 'extras'        => $basket->extras,
        ]);

        //can not use sync as very entry wil be overwritten by the previous one
        //this means we need to detach all before re attaching all when we are updating.
        foreach($basket->extras as $key => $value) {
            $sale->extras()->attach([$value['id'] => ['nr' => $value['nr']]]);
        }



        //sale added, now delete basket
        $basket->delete();

        logger()->channel('info')->info('Sale "'.$sale->ticket_nr.'" created by '.auth()->user()->name);

        return response()->json([
            'message'       => 'New Reservation "'.$sale->ticket_nr. '" created'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'event_id'    => 'required',
            'ticket_id'    => 'required',
            'nr_tickets'  => 'required|min:1',
            'name'        => 'required|min:3',
            'total_amount'=> 'required',

        ]);

        $sale = Sale::findOrFail($request->id);

        //in case we are updating an exisitng reservation we have to check for already made payments
        $total_paid = request('amount_paid') + request('paying_now');

        $sale->event_id           = request('event_id');
        $sale->ticket_id     = request('ticket_id');
        $sale->promocode_id     = request('promocode_id');
        $sale->nr_tickets     = request('nr_tickets');
        $sale->name     = request('name');
        $sale->email     = request('email');
        $sale->phone     = request('phone');
        $sale->country_code     = request('country_code');
        $sale->dial_code     = request('dial_code');
        $sale->lang     = request('lang');
        $sale->amount_paid     = $total_paid;
        $sale->total_amount     = request('total_amount');
        $sale->total_discount     = request('total_discount');
        $sale->guestlist_comments     = request('guestlist_comments');
        $sale->admin_comments     = request('admin_comments');
        $sale->ticket_nr     = request('ticket_nr');




        if (!$sale->update()) {
            return response()->json([
                'message' => 'update failed'
            ], 200);

        }

        //sale is updated, now sync extras
        $sale->extras = request('extras');

        //can not use sync as very entry wil be overwritten by the previous one
        //this means we need to detach all before re attaching all when we are updating.
        $sale->extras()->detach();

        foreach($sale->extras as $key => $value) {
            $sale->extras()->attach([$value['id'] => ['nr' => $value['nr']]]);
        }



        logger()->channel('info')->info('reservation "'.$sale->ticket_nr.'" updated by '.auth()->user()->name);

        return response()->json([
            'message' => 'Reservation "'. $sale->ticket_nr .'" updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sale $sale)
    {
        $sale->extras()->detach();//remove all connected tickets (and delete from pivot table)
            $sale->delete();
            logger()->channel('info')->info('Reservation: "'.$sale->ticket_nr.'" deleted by '.auth()->user()->name);
            session()->flash('alert-success', 'Sale '.$sale->ticket_nr.' deleted');
       // return redirect( route('events.index') );
       return response()->json([
        'message'       => 'Reservation: "'.$sale->ticket_nr.'" deleted '
    ], 200);
    }

    public function allExtrasConnectedToSale($sale_id)
    {
        //get all tickets  connected to a specific sale

        $sale = Sale::findOrFail($sale_id);
        $extras = $sale->extras()->get();//->pluck('extra_id','pivot->nr');


        return response()->json([
            'extras' => $extras,
        ], 200);
    }

    public function getSalesNrTickets($event_id)
    {

        $sales = Sale::where('event_id',$event_id)->pluck('nr_tickets');
            return response()->json([
                'sales'    => $sales,
            ], 200);


    }
}
