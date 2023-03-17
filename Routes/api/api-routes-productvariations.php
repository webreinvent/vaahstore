<?php

/*
 * API url will be: <base-url>/public/api/store/productvariations
 */
Route::group(
    [
        'prefix' => 'store/productvariations',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductVariationsController@getAssets')
        ->name('vh.backend.store.api.productvariations.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductVariationsController@getList')
        ->name('vh.backend.store.api.productvariations.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductVariationsController@updateList')
        ->name('vh.backend.store.api.productvariations.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductVariationsController@deleteList')
        ->name('vh.backend.store.api.productvariations.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductVariationsController@createItem')
        ->name('vh.backend.store.api.productvariations.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductVariationsController@getItem')
        ->name('vh.backend.store.api.productvariations.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductVariationsController@updateItem')
        ->name('vh.backend.store.api.productvariations.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductVariationsController@deleteItem')
        ->name('vh.backend.store.api.productvariations.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductVariationsController@listAction')
        ->name('vh.backend.store.api.productvariations.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductVariationsController@itemAction')
        ->name('vh.backend.store.api.productvariations.item.action');



});
