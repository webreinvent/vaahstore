<?php

/*
 * API url will be: <base-url>/public/api/store/groupcustomers
 */
Route::group(
    [
        'prefix' => 'store/groupcustomers',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'GroupCustomersController@getAssets')
        ->name('vh.backend.store.api.groupcustomers.assets');
    /**
     * Get List
     */
    Route::get('/', 'GroupCustomersController@getList')
        ->name('vh.backend.store.api.groupcustomers.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'GroupCustomersController@updateList')
        ->name('vh.backend.store.api.groupcustomers.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'GroupCustomersController@deleteList')
        ->name('vh.backend.store.api.groupcustomers.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'GroupCustomersController@createItem')
        ->name('vh.backend.store.api.groupcustomers.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'GroupCustomersController@getItem')
        ->name('vh.backend.store.api.groupcustomers.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'GroupCustomersController@updateItem')
        ->name('vh.backend.store.api.groupcustomers.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'GroupCustomersController@deleteItem')
        ->name('vh.backend.store.api.groupcustomers.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'GroupCustomersController@listAction')
        ->name('vh.backend.store.api.groupcustomers.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'GroupCustomersController@itemAction')
        ->name('vh.backend.store.api.groupcustomers.item.action');



});
