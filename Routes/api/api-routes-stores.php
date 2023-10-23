<?php

/*
 * API url will be: <base-url>/public/api/store/stores
 */
Route::group(
    [
        'prefix' => 'store/stores',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'StoresController@getAssets')
        ->name('vh.backend.store.api.stores.assets');
    /**
     * Get List
     */
    Route::get('/', 'StoresController@getList')
        ->name('vh.backend.store.api.stores.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'StoresController@updateList')
        ->name('vh.backend.store.api.stores.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'StoresController@deleteList')
        ->name('vh.backend.store.api.stores.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'StoresController@createItem')
        ->name('vh.backend.store.api.stores.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'StoresController@getItem')
        ->name('vh.backend.store.api.stores.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'StoresController@updateItem')
        ->name('vh.backend.store.api.stores.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'StoresController@deleteItem')
        ->name('vh.backend.store.api.stores.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'StoresController@listAction')
        ->name('vh.backend.store.api.stores.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'StoresController@itemAction')
        ->name('vh.backend.store.api.stores.item.action');



});
