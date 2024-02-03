<?php

/*
 * API url will be: <base-url>/public/api/store/storepaymentmethods
 */
Route::group(
    [
        'prefix' => 'store/storepaymentmethods',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'StorePaymentMethodsController@getAssets')
        ->name('vh.backend.store.api.storepaymentmethods.assets');
    /**
     * Get List
     */
    Route::get('/', 'StorePaymentMethodsController@getList')
        ->name('vh.backend.store.api.storepaymentmethods.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'StorePaymentMethodsController@updateList')
        ->name('vh.backend.store.api.storepaymentmethods.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'StorePaymentMethodsController@deleteList')
        ->name('vh.backend.store.api.storepaymentmethods.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'StorePaymentMethodsController@createItem')
        ->name('vh.backend.store.api.storepaymentmethods.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'StorePaymentMethodsController@getItem')
        ->name('vh.backend.store.api.storepaymentmethods.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'StorePaymentMethodsController@updateItem')
        ->name('vh.backend.store.api.storepaymentmethods.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'StorePaymentMethodsController@deleteItem')
        ->name('vh.backend.store.api.storepaymentmethods.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'StorePaymentMethodsController@listAction')
        ->name('vh.backend.store.api.storepaymentmethods.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'StorePaymentMethodsController@itemAction')
        ->name('vh.backend.store.api.storepaymentmethods.item.action');



});
