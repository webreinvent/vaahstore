<?php

Route::group(
    [
        'prefix' => 'backend/store/paymentmethods',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'PaymentMethodsController@getAssets')
        ->name('vh.backend.store.paymentmethods.assets');
    /**
     * Get List
     */
    Route::get('/', 'PaymentMethodsController@getList')
        ->name('vh.backend.store.paymentmethods.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'PaymentMethodsController@updateList')
        ->name('vh.backend.store.paymentmethods.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'PaymentMethodsController@deleteList')
        ->name('vh.backend.store.paymentmethods.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'PaymentMethodsController@createItem')
        ->name('vh.backend.store.paymentmethods.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'PaymentMethodsController@getItem')
        ->name('vh.backend.store.paymentmethods.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'PaymentMethodsController@updateItem')
        ->name('vh.backend.store.paymentmethods.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'PaymentMethodsController@deleteItem')
        ->name('vh.backend.store.paymentmethods.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'PaymentMethodsController@listAction')
        ->name('vh.backend.store.paymentmethods.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'PaymentMethodsController@itemAction')
        ->name('vh.backend.store.paymentmethods.item.action');

    //---------------------------------------------------------

});
