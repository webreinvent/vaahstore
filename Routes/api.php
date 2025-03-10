<?php

use Illuminate\Http\Request;
use VaahCms\Modules\Store\Http\Controllers\Api\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(
    [
        'prefix'     => '',
        'namespace' => 'Api',
    ],
    function () {
        //------------------------------------------------
        Route::post( '/sign-in/otp', 'AuthController@authGenerateOTP' );
        //------------------------------------------------
        Route::post( '/sign-in', 'AuthController@authSignIn' );
        //------------------------------------------------
        Route::post( '/password-reset/code', 'AuthController@authSendPasswordResetCode' );
        //------------------------------------------------
        Route::post( '/password-reset', 'AuthController@authResetPassword' );

        Route::post( '/sign-out', 'AuthController@authSignOut' )->middleware('auth:sanctum');

        Route::post('/sign-up', 'AuthController@signUp');
    });

include_once __DIR__."/api/api-routes-stores.php";
include_once __DIR__."/api/api-routes-products.php";
include_once __DIR__."/api/api-routes-vendors.php";
include_once __DIR__."/api/api-routes-productvariations.php";
include_once __DIR__."/api/api-routes-productvendors.php";
include_once __DIR__."/api/api-routes-productstocks.php";
include_once __DIR__."/api/api-routes-carts.php";
include_once __DIR__."/api/api-routes-orders.php";
include_once __DIR__."/api/api-routes-payments.php";
include_once __DIR__."/api/api-routes-productattributes.php";
include_once __DIR__."/api/api-routes-productmedias.php";
include_once __DIR__."/api/api-routes-categories.php";
include_once __DIR__."/api/api-routes-brands.php";
include_once __DIR__."/api/api-routes-warehouses.php";
include_once __DIR__."/api/api-routes-attributes.php";
include_once __DIR__."/api/api-routes-attributegroups.php";
include_once __DIR__."/api/api-routes-addresses.php";
include_once __DIR__."/api/api-routes-wishlists.php";
include_once __DIR__."/api/api-routes-users.php";
include_once __DIR__."/api/api-routes-customergroups.php";
include_once __DIR__."/api/api-routes-paymentmethods.php";
include_once __DIR__."/api/api-routes-shipments.php";
include_once __DIR__."/api/api-routes-settings.php";
