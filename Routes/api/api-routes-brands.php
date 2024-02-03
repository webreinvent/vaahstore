<?php

/*
 * API url will be: <base-url>/public/api/store/brands
 */
Route::group(
    [
        'prefix' => 'store/brands',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'BrandsController@getAssets')
        ->name('vh.backend.store.api.brands.assets');
    /**
     * Get List
     */
    Route::get('/', 'BrandsController@getList')
        ->name('vh.backend.store.api.brands.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'BrandsController@updateList')
        ->name('vh.backend.store.api.brands.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'BrandsController@deleteList')
        ->name('vh.backend.store.api.brands.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'BrandsController@createItem')
        ->name('vh.backend.store.api.brands.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'BrandsController@getItem')
        ->name('vh.backend.store.api.brands.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'BrandsController@updateItem')
        ->name('vh.backend.store.api.brands.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'BrandsController@deleteItem')
        ->name('vh.backend.store.api.brands.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'BrandsController@listAction')
        ->name('vh.backend.store.api.brands.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'BrandsController@itemAction')
        ->name('vh.backend.store.api.brands.item.action');



});
