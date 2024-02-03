<?php

/*
 * API url will be: <base-url>/public/api/store/productattributes
 */
Route::group(
    [
        'prefix' => 'store/productattributes',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductAttributesController@getAssets')
        ->name('vh.backend.store.api.productattributes.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductAttributesController@getList')
        ->name('vh.backend.store.api.productattributes.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductAttributesController@updateList')
        ->name('vh.backend.store.api.productattributes.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductAttributesController@deleteList')
        ->name('vh.backend.store.api.productattributes.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductAttributesController@createItem')
        ->name('vh.backend.store.api.productattributes.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductAttributesController@getItem')
        ->name('vh.backend.store.api.productattributes.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductAttributesController@updateItem')
        ->name('vh.backend.store.api.productattributes.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductAttributesController@deleteItem')
        ->name('vh.backend.store.api.productattributes.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductAttributesController@listAction')
        ->name('vh.backend.store.api.productattributes.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductAttributesController@itemAction')
        ->name('vh.backend.store.api.productattributes.item.action');



});
