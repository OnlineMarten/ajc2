@extends('layout.main') {{--@extends('layouts.app')--}}

@section('title', '| my title') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->


<style>

</style>
@endsection

@section('content')
<h1>Dit is een lege pagina</h1>



@endsection
