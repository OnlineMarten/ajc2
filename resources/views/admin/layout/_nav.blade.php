<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('app.name', 'Laravel') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">

            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else

                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('dashboard') }}">Dashboard <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('event.index') }}">Events <span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('ticket.index') }}">Tickets <span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('extra.index') }}">Extras <span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('promocode.index') }}">Promocodes <span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('sale.index') }}">Reservations <span class="sr-only"></span></a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="{{ route('basket.index') }}">Baskets <span class="sr-only"></span></a>
                    </li>

<!--

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            Edit  <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('event.index') }}">
                                {{ __('Events') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('ticket.index') }}">
                                {{ __('Tickets') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('category.index') }}">
                                {{ __('Categories') }}
                            </a>
                            <a class="dropdown-item" href="{{ route('extra.index') }}">
                                {{ __('Extras') }}
                            </a>
                        </div>
                    </li>
                -->

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault();
                                             document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
