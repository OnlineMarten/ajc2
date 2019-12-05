@extends('layout.main') {{--@extends('layouts.app')--}}

@section('title', '| Reservations') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->

    <!--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js" ></script>
    -->

    <!--Adyen-->
    <script src="https://checkoutshopper-test.adyen.com/checkoutshopper/sdk/3.2.0/adyen.js"></script>
    <!-- Styles -->
    <link rel="stylesheet" href="https://checkoutshopper-test.adyen.com/checkoutshopper/sdk/3.2.0/adyen.css"/>

<style>

</style>
@endsection

@section('content')


<booking-component></booking-component>


<!--
<booking-selection></booking-selection>
-->


@endsection
