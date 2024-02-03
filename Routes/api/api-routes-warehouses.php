<?php

/*
 * API url will be: <base-url>/public/api/store/warehouses
 */
Route::group(
    [
        'prefix' => 'store/warehouses',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'WarehousesController@getAssets')
        ->name('vh.backend.store.api.warehouses.assets');
    /**
     * Get List
     */
    Route::get('/', 'WarehousesController@getList')
        ->name('vh.backend.store.api.warehouses.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WarehousesController@updateList')
        ->name('vh.backend.store.api.warehouses.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WarehousesController@deleteList')
        ->name('vh.backend.store.api.warehouses.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'WarehousesController@createItem')
        ->name('vh.backend.store.api.warehouses.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WarehousesController@getItem')
        ->name('vh.backend.store.api.warehouses.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WarehousesController@updateItem')
        ->name('vh.backend.store.api.warehouses.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WarehousesController@deleteItem')
        ->name('vh.backend.store.api.warehouses.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WarehousesController@listAction')
        ->name('vh.backend.store.api.warehouses.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WarehousesController@itemAction')
        ->name('vh.backend.store.api.warehouses.item.action');



});
