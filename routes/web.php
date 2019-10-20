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

Auth::routes();

Route::get('/admin', 'AdminController@index')->name('dashboard');

/*Route::get('/admin/category-show', function () {
    return view('admin/pages/category/category');// not through controller because the data is retrieved through an axios call on page
});
*/

/*
Route::get('/admin/event', 'EventController@index')->name('event');
Route::get('/admin/category', 'CategoryController@index')->name('category');
Route::get('/admin/ticket', 'TicketController@index')->name('ticket');
Route::get('/admin/upsale', 'UpsaleController@index')->name('upsale');
*/
//Route::get('admin/category', function () { return view('admin/pages/category/index'); })->name('category');

//Route::get('admin/category/getAll', 'CategoryController@getAll')->name('category.getAll');



/*ADMIN ONLY*/
Route::group(['middleware' => 'auth'], function () {

    Route::resource('/admin/event', 'EventController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/category', 'CategoryController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/ticket', 'TicketController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/upsale', 'UpsaleController')->only([  'index',  'store', 'update', 'destroy'    ]);

    Route::get('/admin/eventgettickets/{event_id}', 'EventTicketController@allTicketsConnectedToEvent')->name('eventgettickets');
    Route::get('/admin/calendarevents', 'EventController@calendarEvents')->name('calendarEvents');

});//end route group admin
