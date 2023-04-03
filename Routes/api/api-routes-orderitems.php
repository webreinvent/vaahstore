<?php

/*
 * API url will be: <base-url>/public/api/store/orderitems
 */
Route::group(
    [
        'prefix' => 'store/orderitems',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'OrderItemsController@getAssets')
        ->name('vh.backend.store.api.orderitems.assets');
    /**
     * Get List
     */
    Route::get('/', 'OrderItemsController@getList')
        ->name('vh.backend.store.api.orderitems.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'OrderItemsController@updateList')
        ->name('vh.backend.store.api.orderitems.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'OrderItemsController@deleteList')
        ->name('vh.backend.store.api.orderitems.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'OrderItemsController@createItem')
        ->name('vh.backend.store.api.orderitems.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'OrderItemsController@getItem')
        ->name('vh.backend.store.api.orderitems.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'OrderItemsController@updateItem')
        ->name('vh.backend.store.api.orderitems.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'OrderItemsController@deleteItem')
        ->name('vh.backend.store.api.orderitems.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'OrderItemsController@listAction')
        ->name('vh.backend.store.api.orderitems.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'OrderItemsController@itemAction')
        ->name('vh.backend.store.api.orderitems.item.action');



});
