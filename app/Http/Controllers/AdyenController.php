<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
                "value" => $amount
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


        $paymentMethod = $request->paymentDetails;
        // Data object passed from onSubmit event of the front end parsed from JSON to an array

        $params = array(
        "amount" => array(
            "currency" => "EUR",
            "value" => 1000
        ),
        "reference" => "YOUR_ORDER_NUMBER",
        "paymentMethod" => $paymentMethod,
        "returnUrl" => "http://localhost/projects/ajc2/public/checkout",
        "merchantAccount" => "JewelCruises",

        "sessionValidity" => date("c",strtotime('+10 minutes'))
        );

        $result = $service->payments($params);


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
}
