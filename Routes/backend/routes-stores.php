<?php

Route::group(
[
    'prefix' => 'backend/store/stores',

    'middleware' => ['web', 'has.backend.access'],

    'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'StoresController@getAssets')
        ->name('vh.backend.store.storescontroller.assets');
    /**
     * Get List
     */
    Route::get('/', 'StoresController@getList')
        ->name('vh.backend.store.storescontroller.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'StoresController@updateList')
        ->name('vh.backend.store.storescontroller.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'StoresController@deleteList')
        ->name('vh.backend.store.storescontroller.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'StoresController@createItem')
        ->name('vh.backend.store.storescontroller.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'StoresController@getItem')
        ->name('vh.backend.store.storescontroller.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'StoresController@updateItem')
        ->name('vh.backend.store.storescontroller.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'StoresController@deleteItem')
        ->name('vh.backend.store.storescontroller.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'StoresController@listAction')
        ->name('vh.backend.store.storescontroller.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'StoresController@itemAction')
        ->name('vh.backend.store.storescontroller.item.action');
    /**
     * Search store status
     */
    Route::post('/store/status/search', 'StoresController@searchStoreStatus')
        ->name('vh.backend.store.storescontroller.status.search');

    //---------------------------------------------------------

});
