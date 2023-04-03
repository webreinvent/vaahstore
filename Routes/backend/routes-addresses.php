<?php

Route::group(
    [
        'prefix' => 'backend/store/addresses',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'AddressesController@getAssets')
        ->name('vh.backend.store.addresses.assets');
    /**
     * Get List
     */
    Route::get('/', 'AddressesController@getList')
        ->name('vh.backend.store.addresses.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'AddressesController@updateList')
        ->name('vh.backend.store.addresses.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'AddressesController@deleteList')
        ->name('vh.backend.store.addresses.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'AddressesController@createItem')
        ->name('vh.backend.store.addresses.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'AddressesController@getItem')
        ->name('vh.backend.store.addresses.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'AddressesController@updateItem')
        ->name('vh.backend.store.addresses.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'AddressesController@deleteItem')
        ->name('vh.backend.store.addresses.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'AddressesController@listAction')
        ->name('vh.backend.store.addresses.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'AddressesController@itemAction')
        ->name('vh.backend.store.addresses.item.action');

    //---------------------------------------------------------

});
