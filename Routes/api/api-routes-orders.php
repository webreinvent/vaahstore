<?php

/*
 * API url will be: <base-url>/public/api/store/orders
 */
Route::group(
    [
        'prefix' => 'store/orders',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'OrdersController@getAssets')
        ->name('vh.backend.store.api.orders.assets');
    /**
     * Get List
     */
    Route::get('/', 'OrdersController@getList')
        ->name('vh.backend.store.api.orders.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'OrdersController@updateList')
        ->name('vh.backend.store.api.orders.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'OrdersController@deleteList')
        ->name('vh.backend.store.api.orders.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'OrdersController@createItem')
        ->name('vh.backend.store.api.orders.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'OrdersController@getItem')
        ->name('vh.backend.store.api.orders.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'OrdersController@updateItem')
        ->name('vh.backend.store.api.orders.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'OrdersController@deleteItem')
        ->name('vh.backend.store.api.orders.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'OrdersController@listAction')
        ->name('vh.backend.store.api.orders.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'OrdersController@itemAction')
        ->name('vh.backend.store.api.orders.item.action');



});
