<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Sale;
use App\Basket;
use App\Mail\ReservationConfirmation;

class AdyenController extends Controller
{

    public function paymentMethods(Request $request){

        //get values and set standard values if no vaues are sent
        //$amount = filter_var((isset($_POST['amount']) ? $_POST['amount'] : "100"),FILTER_SANITIZE_SPECIAL_CHARS);
        //$countryCode = filter_var((isset($_POST['countryCode']) ? $_POST['countryCode'] : "NL"),FILTER_SANITIZE_SPECIAL_CHARS);
        $amount = $request->amount;
        $countryCode = $request->countryCode;
        $client = new \Adyen\Client();
        $merchantaccount = config('services.adyen.merchantaccount');
        if (config("app.env")=="local") {$client->setEnvironment(\Adyen\Environment::TEST);}
        if (config("app.env")=="production") {$client->setEnvironment(\Adyen\Environment::LIVE);}

        $client->setXApiKey(config('services.adyen.xapikey'));

        $service = new \Adyen\Service\Checkout($client);

        $params = array(
            "merchantAccount" => $merchantaccount,
            "countryCode" => $countryCode,
            "amount" => array(
                "currency" => "EUR",
                "value" => $request->amount,
            ),
            "channel" => "Web",

        );

        $paymentoptions = $service->paymentMethods($params);

        // Pass the response to your front end

        echo json_encode($paymentoptions);

    }

    public function makePayment(Request $request)
    {
        // Set your X-API-KEY with the API key from the Customer Area.
        $client = new \Adyen\Client();
        $client->setEnvironment(\Adyen\Environment::TEST);
        $client->setXApiKey("AQEhhmfuXNWTK0Qc+iSanUk5tfADTM8zr6xRvEMCuQ/epCTxEMFdWw2+5HzctViMSCJMYAc=-e3vvo7GOt54hoBf7sKqYKj/UiWA9WlCooFRpNqQY8HI=-5CUMFg8syXdpW9B5");
        $service = new \Adyen\Service\Checkout($client);

        // Data object passed from onSubmit event of the front end parsed from JSON to an array

        $params = array(
        "amount" => array(
            "currency" => "EUR",
            "value" => $request->amount
        ),

        //these line are only for card testing, need to be romoved when going live
        "additionalData" => array(
            "RequestedTestAcquirerResponseCode"=>13
        ),
        //END these line are only for card testing, need to be romoved when going live

        "paymentMethod" => $request->paymentDetails,
        "returnUrl" => config('app.url')."/checkout",
        "merchantAccount" => "JewelCruises",
        "sessionValidity" => date("c",strtotime('+10 minutes')),
        "countryCode" => $request->countryCode,
        "shopperName" => $request->shopperName,
        "shopperEmail" => $request->shopperEmail,
        "telephoneNumber" => $request->telephoneNumber,
        "shopperStatement" => $request->shopperStatement,
        "countryCode" => $request->countryCode,
        "reference" => $request->reference,
        "merchantOrderReference" => session('basket_id'),//voor adyen om betaalpogingen te kunnen linken
        "shopperIP" => request()->ip(),//voor betere adyen fraud check
        );

      //  logger()->channel('info')->info($params);

        $result = $service->payments($params);

        session(['ticket_nr' => $request->reference]);//used on front to show confirmation

        // Check if further action is needed
        if (array_key_exists("action", $result)){
        // Pass the action object to your front end
        echo json_encode($result);
        }
        else {
        // No further action needed, pass the resultCode to your front end
        // $result['resultCode']
            //echo json_encode($result["resultCode"]);
            echo json_encode($result);
        };
    }


    public function notification(Request $request){

        //log every notification
        logger()->channel('info')->info($request);

       $eventCode = $request->eventCode;

       if (!$eventCode){
           //notification called but not by Adyen?
        logger()->channel('info')->info("Adyen_notification called without eventCode, ignoring.");
        exit;
       }

       //notify adyen we have received the notification
       print "[accepted]";

       //get basket id which is the last part of the ticket number

       $ticket_nr = $request->merchantReference;
       $pieces = explode("-", $ticket_nr);
       if(count($pieces)<4){
            //not a valid ticket number found, we can not process further
            logger()->channel('info')->info('not a valid ticket number, stop processing');
            //send email here
            return;
       }
       $basket_id = $pieces[3]; // piece3
       $basket = Basket::find($basket_id);


       $logtext="Adyen_notification with eventCode: ".$eventCode."/n";

        switch($eventCode){

        case 'AUTHORISATION':
               $logtext .= "AUTHORISATION..";

               // Handle AUTHORISATION notification.
               // Confirms whether the payment was authorised successfully.
               // The authorisation is successful if the "success" field has the value true.
               // In case of an error or a refusal, it will be false and the "reason" field
               // should be consulted for the cause of the authorisation failure.

               //check if authorization is successful
               if ($request->success == "true"){
                   $logtext .= "SUCCESS<br>";

                   //logger()->channel('info')->info('ticketnr'.$ticket_nr .'pieces'.$pieces .'basket_id'.$basket_id .'basket'.$basket);
                   if (!$basket){
                       //basket gone, check if already processed
                       logger()->channel('info')->info('no basket found');
                       $salefound = Sale::where('ticket_nr', $ticket_nr)->count();
                       if (!$salefound){
                           //not processed and no basket, should never happen, log data and send email to admin
                           logger()->channel('info')->info('adyen notification called but no basket and no sale found. ticketnr: '.$ticket_nr);
                           logger()->channel('info')->info($request);
                           //send email here

                           return;
                        }
                       else{
                        $logtext .= "Already processed (".$ticket_nr.")<br>";
                        logger()->channel('info')->info('already processed: '.$ticket_nr);
                           //sale found, so already processed: do nothing
                           //echo "already processed. no further action required<br>";
                       }
                   }
                   else{//basket found

                        //basket found, process sale
                        $logtext.="Basket found. Processing sale...<br>";
/*
                        $basket_id_session = session('basket_id');
                        if($basket_id_session!=$basket_id){
                            //basket ids should be the same, send email warning
                            logger()->channel('info')->info('different basket id in session and in confirmation');

                        }
*/

                        //first update amount paid in basket

                        $sale = new Sale;
                        $sale->amount_paid = $request->value;
                        $sale->pspReference = $request->pspReference;

                        if ($sale->createSale($basket)){
                            $logtext.="Processing sale finished. Success.<br>";

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
                            if ($mail_sent) $logtext.="Mail sent successfully.<br>";

                        }
                        else{
                            $logtext.="Processing sale finished. FAILED! No email sent.<br>";
                            logger()->channel('info')->info('Failed to add sale. No email sent. Ticket nr: '.$ticket_nr);
                            logger()->channel('info')->info($request);
                            //send email here
                        }
                   }
               }

               else{
                   //authorization not successful
                   $logtext .= ".. FAILED.<br>Ticketnr: ". $request->merchantReference . " Reason : ".$request->reason;
               }
            break;

            case 'PENDING':
                $logtext .= "PENDING<br>";
                // Handle PENDING notification.

                $extended = false;

                if ($basket){
                    $basket->extendLifeTime();
                    $extended = true;
                }
                if (!$extended) {
                    logger()->channel('info')->info('extending basket lifetime failed, basket not found');
                }
            break;

       case 'CANCELLATION':
               $logtext .= "CANCELLATION<br>";
               // Handle CANCELLATION notification.
               // Confirms that the payment was cancelled successfully.
           break;

       case 'REFUND':
               $logtext .= "REFUND<br>";
               // Handle REFUND notification.
               // Confirms that the payment was refunded successfully.
           break;

       case 'CANCEL_OR_REFUND':
               $logtext .= "CANCEL_OR_REFUND<br>";
               // Handle CANCEL_OR_REFUND notification.
               // Confirms that the payment was refunded or cancelled successfully.
           break;

       case 'CAPTURE':
               $logtext .= "CAPTURE<br>";
               // Handle CAPTURE notification.
               // Confirms that the payment was successfully captured.
           break;

       case 'REFUNDED_REVERSED':
               $logtext .= "REFUNDED_REVERSED<br>";
               // Handle REFUNDED_REVERSED notification.
               // Tells you that the refund for this payment was successfully reversed.
           break;

       case 'CAPTURE_FAILED':
               $logtext .= "CAPTURE_FAILED<br>";
               // Handle AUTHORISATION notification.
               // Tells you that the capture on the authorised payment failed.
           break;

       case 'REQUEST_FOR_INFORMATION':
               $logtext .= "REQUEST_FOR_INFORMATION<br>";
               // Handle REQUEST_FOR_INFORMATION notification.
               // Information requested for this payment .
           break;

       case 'NOTIFICATION_OF_CHARGEBACK':
               $logtext .= "NOTIFICATION_OF_CHARGEBACK<br>";
               // Handle NOTIFICATION_OF_CHARGEBACK notification.
               // Chargeback is pending, but can still be defended
           break;

       case 'CHARGEBACK':
               $logtext .= "CHARGEBACK<br>";
               // Handle CHARGEBACK notification.
               // Payment was charged back. This is not sent if a REQUEST_FOR_INFORMATION or
               // NOTIFICATION_OF_CHARGEBACK notification has already been sent.
           break;

       case 'CHARGEBACK_REVERSED':
               $logtext .= "CHARGEBACK_REVERSED<br>";
               // Handle CHARGEBACK_REVERSED notification.
               // Chargeback has been reversed (cancelled).
           break;

       case 'REPORT_AVAILABLE':
               $logtext .= "REPORT_AVAILABLE<br>";
               // Handle REPORT_AVAILABLE notification.
               // There is a new report available, the URL of the report is in the "reason" field.
           break;
    }
    logger()->channel('info')->info("Logged info Adyen notification: ".$logtext);

    }

    public function addDirectSale($ticket_nr){
        $logtext="";
        //get basket id
        $basket_id = session('basket_id');
        if ($basket_id) $basket = Basket::find($basket_id);

        if (!$basket){
            //basket gone, check if already processed
            //echo "no basket, checking if already processed...<br>";
            $salefound = Sale::where('ticket_nr', $ticket_nr)->count();
            if (!$salefound){
                //not processed and no basket, should never happen, warn admin
                //echo "not processed, should never happen!!<br>";
                logger()->channel('info')->info('adyen notification called but no basket and no sale found. ticketnr: '.$ticket_nr);
                 exit;
             }
            else{
             $logtext .= "Already processed (".$ticket_nr.")<br>";
                //sale found, so already processed: do nothing
                //echo "already processed. no further action required<br>";
            }
        }
        else{
             //basket found, process sale
             $logtext.="Basket found. Processing sale...<br>";

             //first update amount paid in basket

             $sale = new Sale;
             $sale->pspReference = "direct";

             if ($sale->createSale($basket)){
                 $logtext.="Processing sale finished. Success.<br>";
             }
             else{
                 $logtext.="Processing sale finished. FAILED!<br>";
             }
             //email tickets
             $saleDetails = $sale->getSale();
             \Mail::to('onlinemarten@gmail.com')->send(new ReservationConfirmation($saleDetails));

        }
        logger()->channel('info')->info($logtext);
    }

    public function checkout(Request $request){
        //this function is called from adyen after a redirect, in our case iDeal
        //or it is called from a direct payment, so not going through Adyen.

       // logger()->channel('info')->info($request);
       // logger()->channel('info')->info('ticket_nr from url:'.$request->ticket_nr);

        //first check if this is a direct reservation (no payment needed, so not through Adyen)
        if ($request->direct==true){
            $ticket_nr = $request->ticket_nr;
            //process reservation
            $this->addDirectSale($ticket_nr);
            $resultCode="direct";
            return  view('checkout', compact('ticket_nr', 'resultCode'));
        }

        //sale via Adyen, processing wll run through notification, show confirmation screen only,
        //or go back to booking page if payment failed or refused
        $ticket_nr = session('ticket_nr');
        logger()->channel('info')->info('ticket_nr from session:'.$ticket_nr);
        $back=false;
        $resultCode = $request->resultCode;

        if ($resultCode=="refused"){
            $message = 'Payment refused. Please try a different payment method or credit card';
            $back=true;
        }

        if ($resultCode=="cancelled"){
            $message = 'You have cancelled the payment.';
            $back=true;
        }

        if ($resultCode=="error"){
            $message = 'The payment failed due to technical issues, we could not contact your bank. You can retry.';
            $back=true;
        }

        if ($resultCode=="received"){
            //pending, basket lifetime is extended through Adyen notification handler
            /*
            $extended = false;
            $basket_id_session = session('basket_id');
            if ($basket_id_session){
                $basket = Basket::find($basket_id_session);
                if ($basket){
                   $basket->extendLifeTime();
                   $extended = true;
                }

            }
            if (!$extended) {
                logger()->channel('info')->info('extening basket lifetime failed, basket not found');
            }
            */
        }

        if ($back){
            $basket_id_session = session('basket_id');
            if($basket_id_session){

                $basket = Basket::find($basket_id_session);
                if ($basket){
                    $ref = "booking/".$basket->event_id.'?resultCode='.$resultCode;
                    return redirect()->to($ref)->with('error', $message);//->with('alert', $message);
                }
            }

        }

        return view('checkout', compact('ticket_nr', 'resultCode'));
    }
}
