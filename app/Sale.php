<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    protected $guarded = ['id'];

    use SoftDeletes;

    public function extras()
    {
        //return all extras connected to an sale
        return $this->belongsToMany('App\Extra')->withPivot('nr')->withTimestamps();
    }

    //this function is called from Adyen notification.
    //Please note that there also the amount paid and psp ref are set.
    //If this function will be called from somehwere else, the amount paid could go wrong!!

    public function createSale($basket){

    if (!$basket){
        //no basket, return with error, should never happen as basket was successfully created less than a second ago
        //send email
            return false;
        }

        $this->event_id           = $basket->event_id;
        $this->ticket_id     = $basket->ticket_id;
        $this->promocode_id     = $basket->promocode_id;
        $this->nr_tickets     = $basket->nr_tickets;
        $this->name     = $basket->name;
        $this->email     = $basket->email;
        $this->phone     = $basket->phone;
        $this->country_code     = $basket->country_code;
        $this->dial_code     = $basket->dial_code;
        $this->lang     = $basket->lang;
       // $this->amount_paid     = $basket->amount_paid;
        $this->total_amount     = $basket->total_amount;
        $this->total_discount     = $basket->total_discount;
        $this->guestlist_comments     = $basket->guestlist_comments;
        $this->admin_comments     = $basket->admin_comments;
        $this->ticket_nr     = $basket->ticket_nr;
        $this->save();

        //can not use sync as every entry wil be overwritten by the previous one
        //this means we need to detach all before re attaching all when we are updating.
        foreach($basket->extras as $key => $value) {
            if ($value['nr']){//only add extras with a nr>0, meaning we have sold this extra
                $this->extras()->attach([$value['id'] => ['nr' => $value['nr']]]);
            }

        }



    //sale added, now delete basket
    $basket->delete();

    //update event tickets sold
    $event = Event::find($this->event_id);
    $sold = $event->tickets_sold;
    $event->tickets_sold = $sold + $this->nr_tickets;

    //send warning email if tickets sold > capacity

    $event->save();

    //update event availability
    $event->updateEventAvailability();

    return true;
}

public function getSale(){
    $saleDetails = Sale::where('sales.id',$this->id)
            ->join('events','events.id', '=', 'sales.event_id')
            ->join('tickets','tickets.id', '=', 'sales.ticket_id')
            ->leftjoin('promo_codes','promo_codes.id', '=','sales.promocode_id' )
            //leftjoin uses all columns in left table, even if not match in right table

      //      ->where('events.id','=', $id)

            ->select('sales.*', 'events.event_date','tickets.title as ticket_title' )
            ->get();

        $extras = Sale::find($this->id)->extras()->get();
        if (count($extras)) $saleDetails[0]->extras=$extras;

    return $saleDetails[0];
}
}
