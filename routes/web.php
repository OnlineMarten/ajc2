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

Route::get('/checkout', function () {
    return view('checkout');
});

Route::get('/calendarevents', 'EventController@calendarEvents')->name('calendarEvents');
Route::get('/openevents', 'EventController@openEvents')->name('openEvents');
Route::get('/checkPromoCode', 'PromoCodeController@checkPromoCode')->name('checkPromoCode');
Route::get('/refreshbaskets', 'BasketController@refreshBaskets')->name('refreshbaskets');

Route::get('/booking/{event_id}', function () {
    return view('booking');
});

/*adyen*/
Route::get('/paymentmethods', 'AdyenController@paymentMethods');
Route::post('/makepayment', 'AdyenController@makePayment');
Route::resource('/basket', 'BasketController')->only([  'store', 'update' ]);

Auth::routes();

Route::get('/admin', 'AdminController@index')->name('dashboard');
Route::get('getevent/{event_id}', 'EventController@show')->name('getevent');//axios get event details
Route::get('checkEventAvailable/{event_id}', 'EventController@checkEventAvailable')->name('checkevent');//axios get event available for sale
//Route::get('eventgettickets/{event_id}', 'EventController@allTicketsConnectedToEvent')->name('eventgettickets');
Route::get('ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');
Route::get('checkpromocode/{code}', 'PromoCodeController@checkPromoCode')->name('checkpromocode');//axios check for valid promocode

/*ADMIN ONLY*/
Route::group(['middleware' => 'auth'], function () {

    Route::resource('/admin/event', 'EventController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/category', 'CategoryController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/ticket_group', 'TicketGroupController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/ticket', 'TicketController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/extra', 'ExtraController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/promocode', 'PromoCodeController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('/admin/sale', 'SaleController')->only([  'index',  'store', 'update', 'destroy'    ]);
    Route::resource('admin/basket', 'BasketController')->only([  'index',  'destroy'    ]);

    Route::get('/admin/deletedsales', 'SaleController@deletedSales')->name('deletedsales');
    Route::get('/admin/salegetextras/{sale_id}', 'SaleController@allExtrasConnectedToSale')->name('salegetextras');
    Route::get('/admin/eventgetcategories/{event_id}', 'EventController@allCategoriesConnectedToEvent')->name('eventgetcategories');
    Route::get('/admin/eventgetticketgroups/{event_id}', 'EventController@allTicketgroupsConnectedToEvent')->name('eventgetticketgroups');
    Route::post('/admin/eventcheckavailability', 'EventController@checkAvailability')->name('eventcheckavilability');
    //Route::get('/admin/extragetcategories/{extra_id}', 'ExtraController@allCategoriesConnectedToExtra')->name('extragetcategories');
    Route::get('/admin/categorygetextras/{category_id}', 'CategoryController@allExtrasConnectedToCategory')->name('categorygetextras');
    Route::get('/admin/ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');
    Route::get('/admin/deletesessionbasket', 'BasketController@deleteSessionBasket')->name('deletesessionbasket');
    Route::get('/admin/forcedeletesale/{sale_id}', 'SaleController@forceDeleteSale')->name('forceeletesale');


});//end route group admin
