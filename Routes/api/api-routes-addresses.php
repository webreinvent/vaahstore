<?php

/*
 * API url will be: <base-url>/public/api/store/addresses
 */
Route::group(
    [
        'prefix' => 'store/addresses',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'AddressesController@getAssets')
        ->name('vh.backend.store.api.addresses.assets');
    /**
     * Get List
     */
    Route::get('/', 'AddressesController@getList')
        ->name('vh.backend.store.api.addresses.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'AddressesController@updateList')
        ->name('vh.backend.store.api.addresses.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'AddressesController@deleteList')
        ->name('vh.backend.store.api.addresses.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'AddressesController@createItem')
        ->name('vh.backend.store.api.addresses.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'AddressesController@getItem')
        ->name('vh.backend.store.api.addresses.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'AddressesController@updateItem')
        ->name('vh.backend.store.api.addresses.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'AddressesController@deleteItem')
        ->name('vh.backend.store.api.addresses.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'AddressesController@listAction')
        ->name('vh.backend.store.api.addresses.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'AddressesController@itemAction')
        ->name('vh.backend.store.api.addresses.item.action');



});
