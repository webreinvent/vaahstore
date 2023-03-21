<?php

Route::group(
    [
        'prefix' => 'backend/store/productprices',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductPricesController@getAssets')
        ->name('vh.backend.store.productprices.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductPricesController@getList')
        ->name('vh.backend.store.productprices.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductPricesController@updateList')
        ->name('vh.backend.store.productprices.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductPricesController@deleteList')
        ->name('vh.backend.store.productprices.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductPricesController@createItem')
        ->name('vh.backend.store.productprices.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductPricesController@getItem')
        ->name('vh.backend.store.productprices.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductPricesController@updateItem')
        ->name('vh.backend.store.productprices.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductPricesController@deleteItem')
        ->name('vh.backend.store.productprices.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductPricesController@listAction')
        ->name('vh.backend.store.productprices.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductPricesController@itemAction')
        ->name('vh.backend.store.productprices.item.action');

    //---------------------------------------------------------

});
