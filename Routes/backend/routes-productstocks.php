<?php

Route::group(
    [
        'prefix' => 'backend/store/productstocks',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductStocksController@getAssets')
        ->name('vh.backend.store.productstocks.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductStocksController@getList')
        ->name('vh.backend.store.productstocks.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductStocksController@updateList')
        ->name('vh.backend.store.productstocks.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductStocksController@deleteList')
        ->name('vh.backend.store.productstocks.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductStocksController@createItem')
        ->name('vh.backend.store.productstocks.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductStocksController@getItem')
        ->name('vh.backend.store.productstocks.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductStocksController@updateItem')
        ->name('vh.backend.store.productstocks.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductStocksController@deleteItem')
        ->name('vh.backend.store.productstocks.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductStocksController@listAction')
        ->name('vh.backend.store.productstocks.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductStocksController@itemAction')
        ->name('vh.backend.store.productstocks.item.action');

    //---------------------------------------------------------

});
