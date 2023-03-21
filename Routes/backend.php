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
include_once __DIR__."/backend/routes-productprices.php";
include_once __DIR__."/backend/routes-warehouses.php";
include_once __DIR__."/backend/routes-orders.php";
