@extends('admin.layout.main')

@section('title', '| Categories') <!--title will be appended to title set in ENV file-->

@section('page-specific-scripts-stylesheets')
    <!-- insert page specific scripts here, will be loaded after general scripts-->

@endsection



@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">

                <event-component></event-component>
            </div>
        </div>
    </div>

@endsection
