<?php

/*
 * API url will be: <base-url>/public/api/store/productvendors
 */
Route::group(
    [
        'prefix' => 'store/productvendors',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductVendorsController@getAssets')
        ->name('vh.backend.store.api.productvendors.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductVendorsController@getList')
        ->name('vh.backend.store.api.productvendors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductVendorsController@updateList')
        ->name('vh.backend.store.api.productvendors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductVendorsController@deleteList')
        ->name('vh.backend.store.api.productvendors.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductVendorsController@createItem')
        ->name('vh.backend.store.api.productvendors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductVendorsController@getItem')
        ->name('vh.backend.store.api.productvendors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductVendorsController@updateItem')
        ->name('vh.backend.store.api.productvendors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductVendorsController@deleteItem')
        ->name('vh.backend.store.api.productvendors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductVendorsController@listAction')
        ->name('vh.backend.store.api.productvendors.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductVendorsController@itemAction')
        ->name('vh.backend.store.api.productvendors.item.action');



});
