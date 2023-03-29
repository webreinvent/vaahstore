<?php

/*
 * API url will be: <base-url>/public/api/store/paymentmethods
 */
Route::group(
    [
        'prefix' => 'store/paymentmethods',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'PaymentMethodsController@getAssets')
        ->name('vh.backend.store.api.paymentmethods.assets');
    /**
     * Get List
     */
    Route::get('/', 'PaymentMethodsController@getList')
        ->name('vh.backend.store.api.paymentmethods.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'PaymentMethodsController@updateList')
        ->name('vh.backend.store.api.paymentmethods.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'PaymentMethodsController@deleteList')
        ->name('vh.backend.store.api.paymentmethods.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'PaymentMethodsController@createItem')
        ->name('vh.backend.store.api.paymentmethods.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'PaymentMethodsController@getItem')
        ->name('vh.backend.store.api.paymentmethods.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'PaymentMethodsController@updateItem')
        ->name('vh.backend.store.api.paymentmethods.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'PaymentMethodsController@deleteItem')
        ->name('vh.backend.store.api.paymentmethods.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'PaymentMethodsController@listAction')
        ->name('vh.backend.store.api.paymentmethods.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'PaymentMethodsController@itemAction')
        ->name('vh.backend.store.api.paymentmethods.item.action');



});
