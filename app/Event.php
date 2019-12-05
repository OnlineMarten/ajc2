<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Basket;

class Event extends Model
{

    public function ticketgroup()
    {
        //return the tickegtgroup connected to the event
        return $this->belongsTo('App\TicketGroup','ticket_group_id');//foreign key added because laravel proably expects it to be ticketgroup_id
    }


    public function categories()
    {
        //return all categories connected to an event, sorted by order
        return $this->belongsToMany('App\Category');
    }
/*
    public function tickets()
    {
        //return all tickets connected to an event sorted by order
        return $this->hasManyThrough('App\Ticket', 'App\TicketGroup');
    }
*/

    public function getAvailableTickets($sale_id="",$ignore_reserved=false){
        //EVENT
        if ($this->active){
            //we have an active event, now check sales and baskets, to see how many tickets are still available

        //get all tickets in baskets, excluding basket curently in use
        $basket_id = session('basket_id');
        if ($basket_id){
            $baskets = Basket::where('id', '!=', $basket_id)->where('event_id',$this->id)->pluck('nr_tickets');
            logger()->channel('info')->info('current basket found, ignoring');
        }
        else{
            $baskets = Basket::where('event_id',$this->id)->pluck('nr_tickets');
        }
        logger()->channel('info')->info('all baskets:'.$baskets);

        //get all tickets sold, excluding current sale
        if ($sale_id){
            $sales = Sale::where('id', '!=', $sale_id)->where('event_id',$this->id)->pluck('nr_tickets');
            logger()->channel('info')->info('current sale found, ignoring');
        }
        else{
            $sales = Sale::where('event_id',$this->id)->pluck('nr_tickets');
        }
        logger()->channel('info')->info('all sales:'.$sales);

        //zet alle verkopen in array
        $tables = array();

        if (!$ignore_reserved){//do we need to take reserved seats into account?
            if ($this->tickets_reserved>0){//check if seats are reserved by admin
                //seats are reserved, add them to salestable
                array_push($tables, $this->tickets_reserved);
            //  logger()->channel('info')->info('pushing reserved:'.$this->tickets_reserved);
            }
        }
        foreach ($baskets as $basket){
            if ($basket>0){
                array_push($tables, $basket);
            // logger()->channel('info')->info('pushing basket:'.$basket);
            }
        }
        foreach ($sales as $sale){
            array_push($tables, $sale);
            //logger()->channel('info')->info('pushing sale:'.$sale);
        }
        rsort($tables);//sorteer verkopen van groot naar klein
        foreach ($tables as $table){
            logger()->channel('info')->info('tables:'.$table);
        }

        $remaining_tables = 7; # Het totale aantal tafels op de boot
        $large_table = 1; # De grote tafel voor max 6 pax achterin de boot
        //$max_group_size = 20;
        $nr_of_groups = 0; #aantal boekingen van meer dan 2 personen, mogen er maximaal 3 van op de boot
        $available = 0; # zet beschikbare tickets op nul, wordt aangepast als er beschikbaarheid is
        $largest_group = 0;
        //loop door alle verkopen en pas beschikbare tafels aan
        foreach ($tables as $r){
            //echo $r."<br>";
            //first get groups larger than 6, which can only be added manually
            if ($r>14){
                //zet op uitverkocht
                $large_table = 0;
                $remaining_tables = 0;
                $nr_of_groups+=1;
                $largest_group = $r;
            }
            if (($r>=9) && ($r<=14)){
                //gooi achterste helft dicht
                $large_table=0;
                $remaining_tables -= 4;
                $nr_of_groups+=1;
                $largest_group = $r;
            }
            if (($r>=7) && ($r<=8)){
                //gooi middelste deel dicht
                $remaining_tables -= 4;
                $nr_of_groups+=1;
                $largest_group = $r;
            }

            if (($r>2) && ($r<=6)) {
                $nr_of_groups+=1;
                if ($r>$largest_group) $largest_group = $r;
                if ($large_table==1){
                    $large_table=0;
                }
                else{
                    if (($r==6) or ($r==5)) $remaining_tables-=3;
                    if (($r==4) or ($r==3)) $remaining_tables-=2;
                }
            }
            if (($r==2) or ($r==1)){
                if ($remaining_tables>0){
                    $remaining_tables-=1;
                }
                else{
                    if ($large_table==1){
                        $large_table=0;
                    }
                    else{
                    // trigger_error('Notice from getAvailableTickets: sold too many tables or (part of) reserved seats no longer available. Cruise id: '.$cruise_id,E_USER_ERROR);
                    }
                }
            }
        }

        //alle sales doorgelopen, nu max beschikbare tickes berekenen
        if ($large_table==1){//alleen maar tweetjes verkocht

            if ($remaining_tables == 7) $available = 20;//nog niks verkocht
            if (($remaining_tables >= 4) && ($remaining_tables <= 6)) $available = 14;//max 3 tweetjes verkocht, kunnen voorin
            if ($remaining_tables == 3) $available = 8;//3 tweetjes voorin en 4e tweetje achterin op grote tafel, dan nog tafel voor 8 mogelijk
            if ($remaining_tables < 3) $available = 6;//altijd minimaal 6 tickets beschikbaar omdat grote tafel nog vrij is

        }

        if ($large_table==0){

            if ($remaining_tables==7) $available = 14;//alleen 1 groep van max 6 verkocht

            if ($remaining_tables==6) {
                if (($largest_group == 6) || ($largest_group == 5)) {
                    $available = 8;
                }
                else $available = 14;
            }

            if (($remaining_tables==5) || ($remaining_tables==4)) $available = 8;
            if  ($remaining_tables==3) $available = 6;
            if  ($remaining_tables==2) {
                if ($largest_group<5) $available = 6;//we kunnen het groepje achterin van max 4 personen wel op 2 andere tafles zetten
                else $available = 4;//groep achterin is 5 of 6, kan niet meer verplaatst worden
            }
            if  ($remaining_tables==1) $available = 2;

        }

        //max 3 groepen check
        if ($nr_of_groups>=3){
            if ($remaining_tables>=1){
                $available = 2;
            }
        }
        logger()->channel('info')->info(" remaining large_table = ". $large_table ." remaining small_tables = ". $remaining_tables ." nr of groups ". $nr_of_groups." available tickets: ".$available. "<br>");
            return  $available;
        }
        else{
            return 0;
        }
    }

    public function updateEventAvailability(){
        logger()->channel('info')->info('updating availability');
        $available = $this->getAvailableTickets();
        if ($available ==0){
            //no availability, check for active baskets
            logger()->channel('info')->info('availability=0, checking baskets');
            $baskets = Basket::where('event_id',$this->id)->pluck('nr_tickets');
            logger()->channel('info')->info('baskets:'.$baskets);
            if (count($baskets)){
                //we have baskets, lets check if there are tickets in them
                $nr=0;
                foreach ($baskets as $basket){
                    $nr+=$basket;
                }
                if ($nr==0){
                    $this->sold_out=true;
                    $this->save();
                    logger()->channel('info')->info('only empty baskets, set event to sold out');

                }
                else{
                    logger()->channel('info')->info('we have baskets with tickets ('.$nr.', do nothing');
                }
            }
            else{
                logger()->channel('info')->info('no baskets, set event to sold out');
                $this->sold_out=true;
                $this->save();
            }
        }
        else{//there is still availability
            $this->sold_out=false;
                $this->save();
                logger()->channel('info')->info('set event to open');
        }


    }
}
