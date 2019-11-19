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

<?php echo "de merchant account =".config('services.adyen.merchantaccount');

session(['basket_id' => '3']);
echo session('basket_id');


?>

@endsection





