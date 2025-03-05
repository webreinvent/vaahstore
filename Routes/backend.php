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

Route::group(
    [
        'prefix'     => 'backend/store',
        'middleware' => ['web', 'has.backend.access'],
        'namespace' => 'Backend',
    ],
    function () {
        //------------------------------------------------
        Route::get( '/', 'BackendController@index' )
            ->name( 'vh.backend.store' );
        //------------------------------------------------
        //------------------------------------------------
        Route::get( '/assets', 'BackendController@getAssets' )
            ->name( 'vh.backend.store.assets' );
        //------------------------------------------------
    });

include_once __DIR__."/backend/routes-stores.php";
include_once __DIR__."/backend/routes-vendors.php";
include_once __DIR__."/backend/routes-brands.php";
include_once __DIR__."/backend/routes-products.php";
include_once __DIR__."/backend/routes-productvariations.php";
include_once __DIR__."/backend/routes-productvendors.php";
include_once __DIR__."/backend/routes-productmedias.php";
include_once __DIR__."/backend/routes-warehouses.php";
include_once __DIR__."/backend/routes-productstocks.php";
include_once __DIR__."/backend/routes-attributes.php";
include_once __DIR__."/backend/routes-orders.php";
include_once __DIR__."/backend/routes-attributegroups.php";
include_once __DIR__."/backend/routes-paymentmethods.php";
include_once __DIR__."/backend/routes-storepaymentmethods.php";
include_once __DIR__."/backend/routes-addresses.php";
include_once __DIR__."/backend/routes-customergroups.php";
include_once __DIR__."/backend/routes-wishlists.php";
include_once __DIR__."/backend/routes-productattributes.php";
include_once __DIR__."/backend/routes-attributegroups.php";
include_once __DIR__."/backend/routes-users.php";
include_once __DIR__."/backend/routes-categories.php";
include_once __DIR__."/backend/routes-carts.php";
include_once __DIR__."/backend/routes-settings.php";
include_once __DIR__."/backend/routes-payments.php";
include_once __DIR__."/backend/routes-shipments.php";
include_once __DIR__."/backend/routes-import.php";
include_once __DIR__ . "/backend/routes-dashboard.php";
