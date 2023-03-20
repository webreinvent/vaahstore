<?php

Route::group(
    [
        'prefix' => 'backend/store/attributes',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'AttributesController@getAssets')
        ->name('vh.backend.store.attributes.assets');
    /**
     * Get List
     */
    Route::get('/', 'AttributesController@getList')
        ->name('vh.backend.store.attributes.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'AttributesController@updateList')
        ->name('vh.backend.store.attributes.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'AttributesController@deleteList')
        ->name('vh.backend.store.attributes.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'AttributesController@createItem')
        ->name('vh.backend.store.attributes.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'AttributesController@getItem')
        ->name('vh.backend.store.attributes.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'AttributesController@updateItem')
        ->name('vh.backend.store.attributes.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'AttributesController@deleteItem')
        ->name('vh.backend.store.attributes.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'AttributesController@listAction')
        ->name('vh.backend.store.attributes.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'AttributesController@itemAction')
        ->name('vh.backend.store.attributes.item.action');

    //---------------------------------------------------------

});
