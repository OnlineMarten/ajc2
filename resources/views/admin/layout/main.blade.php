<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    @include('admin.layout._head')

</head>

<body>

    <div id="app">

        @include('admin.layout._nav')

        <div class="container">

                @include('admin.layout._messages')

            @yield('content')

        </div>{{--container--}}

        @include('admin.layout._footer')

    </div>{{--app--}}

    @yield('scripts')
</body>

</html>
