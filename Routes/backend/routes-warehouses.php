<?php

Route::group(
    [
        'prefix' => 'backend/store/warehouses',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'WarehousesController@getAssets')
        ->name('vh.backend.store.warehouses.assets');
    /**
     * Get List
     */
    Route::get('/', 'WarehousesController@getList')
        ->name('vh.backend.store.warehouses.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WarehousesController@updateList')
        ->name('vh.backend.store.warehouses.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WarehousesController@deleteList')
        ->name('vh.backend.store.warehouses.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'WarehousesController@fillItem')
        ->name('vh.backend.store.warehouses.fill');

    /**
     * Create Item
     */
    Route::post('/', 'WarehousesController@createItem')
        ->name('vh.backend.store.warehouses.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WarehousesController@getItem')
        ->name('vh.backend.store.warehouses.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WarehousesController@updateItem')
        ->name('vh.backend.store.warehouses.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WarehousesController@deleteItem')
        ->name('vh.backend.store.warehouses.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WarehousesController@listAction')
        ->name('vh.backend.store.warehouses.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WarehousesController@itemAction')
        ->name('vh.backend.store.warehouses.item.action');

    //---------------------------------------------------------

});
