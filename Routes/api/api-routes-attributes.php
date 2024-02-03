<?php

/*
 * API url will be: <base-url>/public/api/store/attributes
 */
Route::group(
    [
        'prefix' => 'store/attributes',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'AttributesController@getAssets')
        ->name('vh.backend.store.api.attributes.assets');
    /**
     * Get List
     */
    Route::get('/', 'AttributesController@getList')
        ->name('vh.backend.store.api.attributes.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'AttributesController@updateList')
        ->name('vh.backend.store.api.attributes.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'AttributesController@deleteList')
        ->name('vh.backend.store.api.attributes.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'AttributesController@createItem')
        ->name('vh.backend.store.api.attributes.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'AttributesController@getItem')
        ->name('vh.backend.store.api.attributes.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'AttributesController@updateItem')
        ->name('vh.backend.store.api.attributes.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'AttributesController@deleteItem')
        ->name('vh.backend.store.api.attributes.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'AttributesController@listAction')
        ->name('vh.backend.store.api.attributes.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'AttributesController@itemAction')
        ->name('vh.backend.store.api.attributes.item.action');



});
