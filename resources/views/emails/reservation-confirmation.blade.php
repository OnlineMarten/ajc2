@component('mail::message')
# Introduction

<h2>Hello {{$sale->name}}</h2>

<p>This is a confirmation email</p>

<p>Your ticket number: {{$sale->ticket_nr}}</p>

<p>Your tickets: {{$sale->nr_tickets}} x {{$sale->ticket_title}}</p>

@if(!$sale->extras->isEmpty())
<p>Extras</p>
<ul>
    @foreach ($sale->extras as $extra)
        @if ($extra->max=="ticket")
            <li>{{$sale->nr_tickets}} x {{$extra->title}}</li>
        @else
            <li>{{$extra->pivot->nr}} x {{$extra->title}}</li>
        @endif
    @endforeach
</ul>
@endif

@component('mail::button', ['url' => ''])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
