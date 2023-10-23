<?php

Route::group(
    [
        'prefix' => 'backend/store/productvariations',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductVariationsController@getAssets')
        ->name('vh.backend.store.productvariations.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductVariationsController@getList')
        ->name('vh.backend.store.productvariations.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductVariationsController@updateList')
        ->name('vh.backend.store.productvariations.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductVariationsController@deleteList')
        ->name('vh.backend.store.productvariations.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'ProductVariationsController@fillItem')
        ->name('vh.backend.store.productvariations.fill');

    /**
     * Create Item
     */
    Route::post('/', 'ProductVariationsController@createItem')
        ->name('vh.backend.store.productvariations.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductVariationsController@getItem')
        ->name('vh.backend.store.productvariations.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductVariationsController@updateItem')
        ->name('vh.backend.store.productvariations.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductVariationsController@deleteItem')
        ->name('vh.backend.store.productvariations.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductVariationsController@listAction')
        ->name('vh.backend.store.productvariations.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductVariationsController@itemAction')
        ->name('vh.backend.store.productvariations.item.action');

    //---------------------------------------------------------

});
