@extends('layout.main') {{--@extends('layouts.app')--}}

@section('title', '| Reservations') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->

<style>

</style>
@endsection

@section('content')

Hello
<?php

use Whitecube\MultiSafepay\Entities\Order;

$client = new Whitecube\MultiSafepay\Client('test', '3db48b42ebb597147859973c89a691898dd250a2');

$var = $client->orders()->create([
    'order_id' => 'my-order-id2',
    'type' => 'redirect',
    'amount' => 20,
    'currency' => 'EUR',
    'description' => '2 movie tickets',
    'payment_options' => [
        'notification_url' => 'http://www.example.com/client/notification?type=notification',
        'redirect_url' => 'http://www.example.com/client/notification?type=redirect',
        'cancel_url' => 'http://www.example.com/client/notification?type=cancel',
        'close_window' => ''
    ]
]);
return Redirect::to($var->payment_url);




echo $var->payment_url;
//header('Location: $var->payment_url');

//var_dump($client->orders()->fetch('1585994950'));
?>



@endsection



