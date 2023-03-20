<?php

/*
 * API url will be: <base-url>/public/api/store/productprices
 */
Route::group(
    [
        'prefix' => 'store/productprices',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductPricesController@getAssets')
        ->name('vh.backend.store.api.productprices.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductPricesController@getList')
        ->name('vh.backend.store.api.productprices.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductPricesController@updateList')
        ->name('vh.backend.store.api.productprices.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductPricesController@deleteList')
        ->name('vh.backend.store.api.productprices.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductPricesController@createItem')
        ->name('vh.backend.store.api.productprices.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductPricesController@getItem')
        ->name('vh.backend.store.api.productprices.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductPricesController@updateItem')
        ->name('vh.backend.store.api.productprices.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductPricesController@deleteItem')
        ->name('vh.backend.store.api.productprices.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductPricesController@listAction')
        ->name('vh.backend.store.api.productprices.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductPricesController@itemAction')
        ->name('vh.backend.store.api.productprices.item.action');



});
