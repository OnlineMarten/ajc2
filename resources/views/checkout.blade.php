@extends('layout.main') {{--@extends('layouts.app')--}}

@section('title', '| Reservations') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->



<style>

</style>
@endsection

@section('content')



<h1>Thank you for your reservation</h1>

<p>Hello {{ $name }}</p>
 <p>Thank you for your reservation</p>
 <p>Your confirmation number is: {{ $ticket_nr }}</p>
 <p>An email, which serves as your ticket, has been sent to: {{ $email }}</p>


 <p></p>


@endsection
