<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    @include('layout._head')

</head>

<body>

    <div id="app">

        @include('layout._nav')

        <div class="container">

                @include('layout._messages')

            @yield('content')

        </div>{{--container--}}

        @include('layout._footer')

    </div>{{--app--}}

    @yield('scripts')
</body>

</html>
