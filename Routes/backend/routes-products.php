<?php

Route::group(
    [
        'prefix' => 'backend/store/products',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductsController@getAssets')
        ->name('vh.backend.store.products.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductsController@getList')
        ->name('vh.backend.store.products.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductsController@updateList')
        ->name('vh.backend.store.products.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductsController@deleteList')
        ->name('vh.backend.store.products.list.delete');


    /**
     * POST get attribute list
     */
    Route::post('/getAttributeList', 'ProductsController@getAttributeList')
        ->name('vh.backend.store.products.getAttributeList');

    /**
     * POST get attribute values
     */
    Route::post('/getAttributeValue', 'ProductsController@getAttributeValue')
        ->name('vh.backend.store.products.getAttributeValue');

    /**
     * Create Item
     */
    Route::post('/', 'ProductsController@createItem')
        ->name('vh.backend.store.products.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductsController@getItem')
        ->name('vh.backend.store.products.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductsController@updateItem')
        ->name('vh.backend.store.products.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductsController@deleteItem')
        ->name('vh.backend.store.products.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductsController@listAction')
        ->name('vh.backend.store.products.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductsController@itemAction')
        ->name('vh.backend.store.products.item.action');

    //---------------------------------------------------------

});
