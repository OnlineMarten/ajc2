@component('mail::message')
# Introduction

<h2>Hello</h2>

<p>This is a message from Cruise Control, I'm sorry, I could not work it out:</p>

<p>{{$message}}</p>


Thanks,<br>
{{ config('app.name') }}
@endcomponent
