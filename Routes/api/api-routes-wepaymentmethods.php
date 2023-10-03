<?php

/*
 * API url will be: <base-url>/public/api/store/wepaymentmethods
 */
Route::group(
    [
        'prefix' => 'store/wepaymentmethods',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'WePaymentMethodsController@getAssets')
        ->name('vh.backend.store.api.wepaymentmethods.assets');
    /**
     * Get List
     */
    Route::get('/', 'WePaymentMethodsController@getList')
        ->name('vh.backend.store.api.wepaymentmethods.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WePaymentMethodsController@updateList')
        ->name('vh.backend.store.api.wepaymentmethods.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WePaymentMethodsController@deleteList')
        ->name('vh.backend.store.api.wepaymentmethods.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'WePaymentMethodsController@createItem')
        ->name('vh.backend.store.api.wepaymentmethods.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WePaymentMethodsController@getItem')
        ->name('vh.backend.store.api.wepaymentmethods.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WePaymentMethodsController@updateItem')
        ->name('vh.backend.store.api.wepaymentmethods.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WePaymentMethodsController@deleteItem')
        ->name('vh.backend.store.api.wepaymentmethods.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WePaymentMethodsController@listAction')
        ->name('vh.backend.store.api.wepaymentmethods.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WePaymentMethodsController@itemAction')
        ->name('vh.backend.store.api.wepaymentmethods.item.action');



});
