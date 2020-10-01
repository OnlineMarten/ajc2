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

Route::get('/payment', function () {
    return view('payment');
});

//events
Route::get('/booking/{event_id}', function () {
    return view('booking');
});
Route::get('/calendarevents', 'EventController@calendarEvents')->name('calendarEvents');
Route::get('/openevents', 'EventController@openEvents')->name('openEvents');
Route::get('/allevents', 'EventController@allEvents')->name('allEvents');
Route::post('eventcheckavailability', 'EventController@checkAvailability')->name('eventcheckavailability');
Route::get('checkEventAvailable/{event_id}', 'EventController@checkEventAvailable')->name('checkevent');//axios get event available for sale
Route::get('getevent/{event_id}', 'EventController@show')->name('getevent');//axios get event details

//tickets
Route::get('ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');

//basket
Route::get('/refreshbaskets', 'BasketController@refreshBaskets')->name('refreshbaskets');
Route::post('checkbasketcomplete', 'BasketController@checkBasketComplete')->name('checkbasketcomplete');
Route::get('getsessionbasket', 'BasketController@getSessionBasket')->name('getsessionbasket');
Route::resource('/basket', 'BasketController')->only([  'store', 'update' ]);
Route::get('extendbasketlifetime', 'BasketController@extendBasketLifetime')->name('extendbasketlifetime');


//promocode
//Route::get('/checkPromoCode', 'PromoCodeController@checkPromoCode')->name('checkPromoCode');
Route::get('checkpromocode/{code}', 'PromoCodeController@checkPromoCode')->name('checkpromocode');//axios check for valid promocode


/*adyen*/
Route::get('/paymentmethods', 'AdyenController@paymentMethods');
Route::post('/makepayment', 'MultiSafePay@makePayment');
Route::get('/checkout', 'MultiSafePay@checkout');


Auth::routes();
Route::get('/admin', 'AdminController@index')->name('dashboard');





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

    //sales
    Route::get('/admin/deletedsales', 'SaleController@deletedSales')->name('deletedsales');
    Route::get('/admin/salegetextras/{sale_id}', 'SaleController@allExtrasConnectedToSale')->name('salegetextras');
    Route::get('/admin/getsale/{sale_id}', 'SaleController@getSale')->name('getsale');
    Route::get('/admin/forcedeletesale/{sale_id}', 'SaleController@forceDeleteSale')->name('forcedeletesale');
    Route::get('/admin/getsales/{event_id}', 'SaleController@getSales')->name('getsales');
    Route::get('/admin/saletickets/{sale_id}', 'SaleController@emailTickets')->name('saletickets');
    //event
    Route::get('/admin/eventgetcategories/{event_id}', 'EventController@allCategoriesConnectedToEvent')->name('eventgetcategories');
    Route::get('/admin/adjustreservedtickets', 'EventController@adjustReservedTickets')->name('adjustreservedtickets');
    Route::get('/admin/eventgetticketgroups/{event_id}', 'EventController@allTicketgroupsConnectedToEvent')->name('eventgetticketgroups');
    Route::get('/admin/getevents/{all}', 'EventController@getEvents')->name('getevents');
    //category
    Route::get('/admin/categorygetextras/{category_id}', 'CategoryController@allExtrasConnectedToCategory')->name('categorygetextras');
    //tickets
    Route::get('/admin/ticketgroupgettickets/{ticket_group_id}', 'TicketGroupController@allTicketsConnectedToTicketGroup')->name('ticketgroupgettickets');
    //basket
    Route::get('/admin/deletesessionbasket', 'BasketController@deleteSessionBasket')->name('deletesessionbasket');

    Route::get('mail', function () {
        $sale = App\Sale::find(66);

        return new App\Mail\ReservationConfirmation($sale);
    });


});//end route group admin
