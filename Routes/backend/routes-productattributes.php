<?php

Route::group(
    [
        'prefix' => 'backend/store/productattributes',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductAttributesController@getAssets')
        ->name('vh.backend.store.productattributes.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductAttributesController@getList')
        ->name('vh.backend.store.productattributes.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductAttributesController@updateList')
        ->name('vh.backend.store.productattributes.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductAttributesController@deleteList')
        ->name('vh.backend.store.productattributes.list.delete');

    /**
     * GET Attribute List
     */
    Route::get('/getAttributeValue/{id}', 'ProductAttributesController@getAttributeValue')
        ->name('vh.backend.store.productattributes.list.getAttributeValue');


    /**
     * Create Item
     */
    Route::post('/', 'ProductAttributesController@createItem')
        ->name('vh.backend.store.productattributes.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductAttributesController@getItem')
        ->name('vh.backend.store.productattributes.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductAttributesController@updateItem')
        ->name('vh.backend.store.productattributes.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductAttributesController@deleteItem')
        ->name('vh.backend.store.productattributes.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductAttributesController@listAction')
        ->name('vh.backend.store.productattributes.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductAttributesController@itemAction')
        ->name('vh.backend.store.productattributes.item.action');

    //---------------------------------------------------------

});
