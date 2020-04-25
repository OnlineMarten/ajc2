<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Whitecube\MultiSafepay\Client;
//use Whitecube\MultiSafepay\Entities\Order;
use App\Sale;
use App\Basket;
use App\Mail\ReservationConfirmation;
use App\Mail\AdminMessage;

class MultiSafePay extends Controller
{

    private function newClient()
    {
        if (config("app.env")=="local") $client = new Client(config('services.msp.test_env'), config('services.msp.api_key'));
        if (config("app.env")=="production") $client = new Client(config('services.msp.prod_env'), config('services.msp.api_key'));
        return $client;

    }

    public function makePayment(Request $request)
    {
        logger()->channel('info')->info('in makepayment multisafepay');


        //logger()->channel('info')->info($request->ticket_nr);
        //logger()->channel('info')->info($request->amount);

        $client = $this->newClient();

        $order = $client->orders()->create([

            'order_id' => $request->ticket_nr,
            'type' => 'redirect',
            'amount' => $request->amount,
            'currency' => 'EUR',
            'description' => $request->shopperStatement,

            "google_analytics" => [
                "account"=> config('services.google.account_id')
                ],

            'payment_options' => [
                'notification_url' => 'https://marten-228e90f6.localhost.run/ajc2/public/api/notification',//TODO wijzigen url en in config zetten
                'redirect_url' => config('app.url')."/checkout",
                'cancel_url' => config('app.url')."/booking/".$request->event_id."?type=cancel",
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

            "seconds_active" => config('custom.psp_lifetime'),//can be set in config/custom.php
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

        //logger()->channel('info')->info("New notification");
        //logger()->channel('info')->info($request);

       $transactionid = $request->transactionid;
       logger()->channel('info')->info("transactionid = ".$transactionid);

       if (!$transactionid){
           //notification called but not by msp?
            logger()->channel('info')->info("Notification called without transactionid, ignoring.");
            return;
       }

       $client = $this->newClient();
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
                try{
                    \Mail::to('onlinemarten@gmail.com')->send(new AdminMessage("WARNING: No basket and no sale found. Ticketnr: ".$ticket_nr));
                }
                catch(\Exception $e){
                    // Get error here
                    logger()->channel('info')->info("Sending mail failed. Ticket number: ".$ticket_nr. " error details: ".$e);
                    $mail_sent = false;
                    //TODO send admin email here with error (e)
                }

                return;
             }
            else{
                logger()->channel('info')->info('Already processed, no further action. Ticket number: '.$ticket_nr);
                //sale found, so already processed: do nothing
            }
            return;
        }

        //we have a basket, now process it
        logger()->channel('info')->info('processing sale');
        $sale = new Sale;
        $sale->amount_paid = $order->amount;
        $sale->pspReference = $order->transaction_id;

        if ($sale->createSale($basket)){
            logger()->channel('info')->info('Sale added');

            //email tickets
            $saleDetails = $sale->getSale();
            $mail_sent = true;
            try{
                \Mail::to('onlinemarten@gmail.com')->send(new ReservationConfirmation($saleDetails));
            }
            catch(\Exception $e){
                // Get error here
                logger()->channel('info')->info("Sending mail failed. Ticket number: ".$ticket_nr. " error details: ".$e);
                $mail_sent = false;
                //TODO send admin email here with error (e)
            }
            if ($mail_sent){
                logger()->channel('info')->info("Mail sent successfully");
                $sale->updateTicketSent();// update the sent ticket timestamp in sale
            }
        }
        else{
            logger()->channel('info')->info('Failed to add sale. No email sent. Ticket nr: '.$ticket_nr);
            logger()->channel('info')->info(print_r($order, true));
            //TODO send admin email here
        }
    }
    else{//order status is not completed, update status in basket

        logger()->channel('info')->info('order status: '. $order->status);

        $basket =  Basket::where('ticket_nr', $order->order_id)->first();
        if ($basket){
            $basket->updateStatus($order->status);
            logger()->channel('info')->info('updated status in basket.');
        }
        else{
            logger()->channel('info')->info("trying to update basket status, but could not find basket:".print_r($order, true));
            //TODO mail admin?

        }
       // logger()->channel('info')->info(print_r($order, true));
    }
}


public function checkout(Request $request){
    //this function is called from msp after a succesfull payment
    //or it is called from a direct payment, so not going through a psp.

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
