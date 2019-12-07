<?php

namespace App\Http\Controllers;

use App\Sale;
use App\Basket;
use App\Event;
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
                return response()->json([
                    'sales'    => $sales,
                ], 200);
            }

            return view('admin.sale');

    }

    public function deletedSales()
    {
        //used from router to display page and from an axios call to get the (initial) data


            $sales = Sale::onlyTrashed()
            ->join('events','events.id', '=', 'sales.event_id')
            ->join('tickets','tickets.id', '=', 'sales.ticket_id')
            ->leftjoin('promo_codes','promo_codes.id', '=','sales.promocode_id' )
            //leftjoin uses all columns in left table, even if not match in right table

      //      ->where('events.id','=', $id)

            ->select('sales.*', 'events.event_date','tickets.title as ticket_title' )
                    ->orderby('created_at','desc')
            ->get();
                return response()->json([
                    'sales'    => $sales,
                ], 200);
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

        //can not use sync as every entry wil be overwritten by the previous one
        //this means we need to detach all before re attaching all when we are updating.
        foreach($basket->extras as $key => $value) {
            if ($value['nr']>0){//only add extras which have n=been sold, so nr>0
                $sale->extras()->attach([$value['id'] => ['nr' => $value['nr']]]);
            }
        }



        //sale added, now delete basket
        $basket->delete();


        $event = Event::find($sale->event_id);

        //update event tickets sold
        $sold = $event->tickets_sold;
        $event->tickets_sold = $sold + $sale->nr_tickets;
        $event->save();

        //update event availability
        $event->updateEventAvailability();


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


        $restored_sale = false;
        $sale = Sale::withTrashed()->findOrFail($request->id);
        $old_event_id = $sale->event_id;
        $old_nr_tickets = $sale->nr_tickets;
        //do a restore if sale is trashed
        if ($sale->trashed()) {
            $sale->restore();
            $restored_sale = true;//if a sale is being restored we do not have to check event change as it was not listed at an event anymore
        }

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

            if ($value['nr']>0){//only add extras with a nr>0, meaning we have sold this extra
                $sale->extras()->attach([$value['id'] => ['nr' => $value['nr']]]);
            }
        }

        //if we do not have a restored sale we have to heck for an event change

        //update event availability
        $event = Event::find($sale->event_id);

        if (!$restored_sale){//no restored sale

            $old_event = Event::find($old_event_id);

            if ($old_event_id!=$sale->event_id){
                //we have a date change, update tickets and availability in old and new event

                //update old event deduct old tickets sold
                $old_event->tickets_sold -= $old_nr_tickets;
                $old_event->save();
                $old_event->updateEventAvailability();

                //new event add new tickets sold
                $event->tickets_sold+=$sale->nr_tickets;
            }

            else{//not restored, no date change, deduct old nr tickets and add new nr
                $sold =$event->tickets_sold;
                $event->tickets_sold = $sold - $old_nr_tickets + $sale->nr_tickets;
            }

        }

        else{//restored sale, date change unimportant, just add new tickets
            $event->tickets_sold += $sale->nr_tickets;
        }
        //update event
        $event->save();
        $event->updateEventAvailability();

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
      //  $sale->extras()->detach();//remove all connected tickets (and delete from pivot table)



        //update event availability
        $event = Event::find($sale->event_id);

        //update event tickets sold
        $sold = $event->tickets_sold;
        $event->tickets_sold = $sold - $sale->nr_tickets;
        $event->save();
        $event->updateEventAvailability();
        $sale->delete();

        logger()->channel('info')->info('Reservation: "'.$sale->ticket_nr.'" soft deleted by '.auth()->user()->name);
        session()->flash('alert-success', 'Sale '.$sale->ticket_nr.' deleted');

        return response()->json([
            'message'       => 'Reservation: "'.$sale->ticket_nr.'" moved to deleted reservations '
        ], 200);
    }

    public function forceDeleteSale( $sale_id)
    {

        $sale = Sale::withTrashed()->findOrFail($sale_id);

        if ($sale->trashed()) {

            $sale->extras()->detach();//remove all connected extras (and delete from pivot table)
            $sale->forceDelete();
            //logger()->channel('info')->info('found trashed sale, forcedeleted it');
        }

        else{
            logger()->channel('info')->info('could not find trashed sale!');
            return response()->json([
                'message'       => 'Reservation: "'.$sale->ticket_nr.'" could not be deleted! '
            ], 200);

        }

        logger()->channel('info')->info('Reservation: "'.$sale->ticket_nr.'" permanently deleted by '.auth()->user()->name);
       // return redirect( route('events.index') );
       return response()->json([
            'message'       => 'Reservation: "'.$sale->ticket_nr.'" permanently deleted '
        ], 200);
    }


    public function allExtrasConnectedToSale($sale_id)
    {
        //get all tickets  connected to a specific sale

        $sale = Sale::withTrashed()->findOrFail($sale_id);
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
