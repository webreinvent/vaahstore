<?php

Route::group(
    [
        'prefix' => 'backend/store/productvendors',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductVendorsController@getAssets')
        ->name('vh.backend.store.productvendors.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductVendorsController@getList')
        ->name('vh.backend.store.productvendors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductVendorsController@updateList')
        ->name('vh.backend.store.productvendors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductVendorsController@deleteList')
        ->name('vh.backend.store.productvendors.list.delete');

    /**
     * POST Product list for store
     */
    Route::post('/getProductForStore', 'ProductVendorsController@productForStore')
        ->name('vh.backend.store.productvendors.list.productForStore');


    /**
     * Create Item
     */
    Route::post('/', 'ProductVendorsController@createItem')
        ->name('vh.backend.store.productvendors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductVendorsController@getItem')
        ->name('vh.backend.store.productvendors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductVendorsController@updateItem')
        ->name('vh.backend.store.productvendors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductVendorsController@deleteItem')
        ->name('vh.backend.store.productvendors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductVendorsController@listAction')
        ->name('vh.backend.store.productvendors.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductVendorsController@itemAction')
        ->name('vh.backend.store.productvendors.item.action');

    //---------------------------------------------------------

});
