<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Whitecube\MultiSafepay\Client;
//use Whitecube\MultiSafepay\Entities\Order;
use App\Sale;
use App\Basket;
use App\Mail\ReservationConfirmation;

class MultiSafePay extends Controller
{
    public function makePayment(Request $request)
    {
        logger()->channel('info')->info('in makepayment multisafepay');
        logger()->channel('info')->info($request->reference);
        logger()->channel('info')->info($request->amount);

        $client = new Client('test', '3db48b42ebb597147859973c89a691898dd250a2');

        $order = $client->orders()->create([

            'order_id' => $request->reference,
            'type' => 'redirect',
            'amount' => $request->amount,
            'currency' => 'EUR',
            'description' => $request->shopperStatement,

            "google_analytics" => [
                "account"=> "UA-XXXXXXXXX" //nog invullen
                ],

            'payment_options' => [
                'notification_url' => 'https://marten-d8db7a49.localhost.run/ajc2/public/api/notification',
                'redirect_url' => 'http://localhost/ajc2/public/checkout',
                'cancel_url' => 'http://localhost/ajc2/public/booking/28?type=cancel',
                'close_window' => ''
                ],

            'customer' => [
                'ip_address' => request()->ip(),
                "locale" => "nl_NL",
                'first_name' => $request->shopperName,
                'country' => $request->countryCode,
                'phone' => $request->telephoneNumber,
                'email' => $request->shopperEmail,


                ],

            "seconds_active" => 30,
            "var1" => $request->tickets
        ]);

       // logger()->channel('info')->info("order=".$order);

       //add some details to session to show on confirmation screen
       session(['shopperName' => $request->shopperName]);
       session(['shopperEmail' => $request->shopperEmail]);

        return response()->json(
             $order
        , 200);
    }

    public function notification(Request $request){

        //log every notification
        logger()->channel('info')->info( "New notification");
        $logtext= "new notification";
        //logger()->channel('info')->info("New notification");
      //  logger()->channel('info')->info($request);

       $transactionid = $request->transactionid;

       if (!$transactionid){
           //notification called but not by msp?
            logger()->channel('info')->info("Notification called without transactionid, ignoring.");
            return;
       }

       $client = new Client('test', '3db48b42ebb597147859973c89a691898dd250a2');
       $order = $client->orders()->fetch($transactionid);
      // logger()->channel('info')->info(print_r($order, true));

       if ($order->status){
            //notify msp we have received the notification
            echo "ok";
       }
       else{//no order status found, ignoring
            logger()->channel('info')->info("No order status found, ignoring.");
            return;
       }

       if ($order->status == "initialized")
       {
           //this is sent when a customer goes to 3d verification of a cc payment?
        logger()->channel('info')->info('Order status: initialized, no action taken');
        return;
       }

       if ($order->status == "completed")
       {
        logger()->channel('info')->info('Order status: completed');


        //get basket id which is the last part of the ticket number

        $ticket_nr = $order->order_id;
        $pieces = explode("-", $ticket_nr);
        if(count($pieces)<4){
                //not a valid ticket number found, we can not process further
                logger()->channel('info')->info('Order contains not a valid ticket number, stop processing');
                //TODO send email here
                return;
        }
        $basket_id = $pieces[3]; // piece3
        $basket = Basket::find($basket_id);

        if (!$basket){
            //basket gone, check if already processed
           // logger()->channel('info')->info('no basket found');
            $salefound = Sale::where('ticket_nr', $ticket_nr)->count();
            if (!$salefound){
                //not processed and no basket, should never happen, log data and send email to admin
                logger()->channel('info')->info('WARNING: No basket and no sale found. Ticketnr: '.$ticket_nr);
                logger()->channel('info')->info(print_r($order, true));
                //TODO send email here

                return;
             }
            else{
                $logtext .= "Already processed (".$ticket_nr.")<br>";
                logger()->channel('info')->info('Already processed, no further action. Ticket number: '.$ticket_nr);
                //sale found, so already processed: do nothing
            }
            return;
        }
        //we have a basket, now process it
        $logtext="msp_notification with transactionid: ".$order->transaction_id."/n";
        logger()->channel('info')->info('processing sale');
        $sale = new Sale;
        $sale->amount_paid = $order->amount;
        $sale->pspReference = $order->transaction_id;

        if ($sale->createSale($basket)){
            $logtext.="Sale added. Success.<br>";
            logger()->channel('info')->info('Sale added');
            //email tickets
            $saleDetails = $sale->getSale();
            $mail_sent = true;
            try{
                \Mail::to('onlinemarten@gmail.com')->send(new ReservationConfirmation($saleDetails));
            }
            catch(\Exception $e){
                // Get error here
                $logtext.="Sending mail failed.<br>";
                $mail_sent = false;
            }
            if ($mail_sent){
                $logtext.="Mail sent successfully.<br>";
                //here update the sent ticket field in sale
                $sale->updateTicketSent();
            }
        }
        else{
            $logtext.="Processing sale finished. FAILED! No email sent.<br>";
            logger()->channel('info')->info('Failed to add sale. No email sent. Ticket nr: '.$ticket_nr);
            logger()->channel('info')->info(print_r($order, true));
            //send email here
        }
    }
    else{//order status is not completed

        logger()->channel('info')->info('order status: '. $order->status.'. No action taken.');
        logger()->channel('info')->info(print_r($order, true));
    }
}


public function checkout(Request $request){
    //this function is called from adyen after a redirect, in our case iDeal
    //or it is called from a direct payment, so not going through Adyen.

    logger()->channel('info')->info($request);
   // logger()->channel('info')->info('ticket_nr from url:'.$request->ticket_nr);

   $ticket_nr = $request->transactionid;
   $name = session('shopperName');
   $email = session('shopperEmail');
  // logger()->channel('info')->info('shopperName: '.$name.' ticketnr: '. $ticket_nr);

    //first check if this is a direct reservation (no payment needed, so not through msp)
    //if sale is via msp, processing wll run through notification
    if ($request->direct==true){
        //process reservation
        $this->addDirectSale($ticket_nr);
    }


    //show confirmaion screen
    return view('checkout', compact('ticket_nr', 'name', 'email'));
}

}
