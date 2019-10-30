<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/calendarevents', 'EventController@calendarEvents')->name('calendarEvents');

Route::get('/booking/{event_id}', function () {
    return view('booking');
});
Auth::routes();

Route::get('/admin', 'AdminController@index')->name('dashboard');
Route::get('getevent/{event_id}', 'EventController@show')->name('getevent');//axios get event details
//Route::get('eventgettickets/{event_id}', 'EventController@allTicketsConnectedToEvent')->name('eventgettickets');
Route::get('ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');

/*ADMIN ONLY*/
Route::group(['middleware' => 'auth'], function () {

    Route::resource('/admin/event', 'EventController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/category', 'CategoryController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/ticket_group', 'TicketGroupController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/ticket', 'TicketController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/extra', 'ExtraController')->only([  'index',  'store', 'update', 'destroy'    ]);


    Route::get('/admin/eventgetcategories/{event_id}', 'EventController@allCategoriesConnectedToEvent')->name('eventgetcategories');
    Route::get('/admin/eventgetticketgroups/{event_id}', 'EventController@allTicketgroupsConnectedToEvent')->name('eventgetticketgroups');
    //Route::get('/admin/extragetcategories/{extra_id}', 'ExtraController@allCategoriesConnectedToExtra')->name('extragetcategories');
    Route::get('/admin/categorygetextras/{category_id}', 'CategoryController@allExtrasConnectedToCategory')->name('categorygetextras');
    Route::get('/admin/ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');



});//end route group admin
