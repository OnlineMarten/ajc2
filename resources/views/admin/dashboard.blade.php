@extends('admin.layout.main') {{--@extends('layouts.app')--}}

@section('title', '| Dashboard') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js" ></script>

<style>

</style>
@endsection

@section('content')
<h1>Dit is het dashboard</h1>

<!--
<h2>Calendar</h2>
<calendar-component></calendar-component>
-->

<hr>
<p>Config vars</p>
<?php echo "minimal number of tickets required: ". config('custom.minimal_tickets_required'); ?>
<hr>
<h2></h2>
<hr>
<booking-selection></booking-selection>

<?php echo "de merchant account =".config('services.adyen.merchantaccount')."<br><br>";

session(['basket_id' => '3']);
echo session('basket_id');

//$extras = array [{id: 1, nr: 2}, {id: 4, nr: 2}, {id: 5, nr: 8}, {id: 3, nr: 3}, {id: 2, nr: 2}];
?>
<hr>
<?php

$arr = Array('id' => 1, 'nr' => 5,'id' => 2, 'nr' => 6);
foreach($arr as $key => $value) {
    echo $key." ";
    echo $value."<br>";
}

echo "app url:". config('app.url');
echo "<br>";
echo "google: " . config('services.google.account_id');

echo "random number: ".time();
?>

@endsection





