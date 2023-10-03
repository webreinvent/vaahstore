<?php

Route::group(
    [
        'prefix' => 'backend/store/wepaymentmethods',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'WePaymentMethodsController@getAssets')
        ->name('vh.backend.store.wepaymentmethods.assets');
    /**
     * Get List
     */
    Route::get('/', 'WePaymentMethodsController@getList')
        ->name('vh.backend.store.wepaymentmethods.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WePaymentMethodsController@updateList')
        ->name('vh.backend.store.wepaymentmethods.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WePaymentMethodsController@deleteList')
        ->name('vh.backend.store.wepaymentmethods.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'WePaymentMethodsController@fillItem')
        ->name('vh.backend.store.wepaymentmethods.fill');

    /**
     * Create Item
     */
    Route::post('/', 'WePaymentMethodsController@createItem')
        ->name('vh.backend.store.wepaymentmethods.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WePaymentMethodsController@getItem')
        ->name('vh.backend.store.wepaymentmethods.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WePaymentMethodsController@updateItem')
        ->name('vh.backend.store.wepaymentmethods.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WePaymentMethodsController@deleteItem')
        ->name('vh.backend.store.wepaymentmethods.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WePaymentMethodsController@listAction')
        ->name('vh.backend.store.wepaymentmethods.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WePaymentMethodsController@itemAction')
        ->name('vh.backend.store.wepaymentmethods.item.action');

    //---------------------------------------------------------

});
