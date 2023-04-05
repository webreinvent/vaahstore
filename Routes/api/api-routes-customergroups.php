<?php

/*
 * API url will be: <base-url>/public/api/store/customergroups
 */
Route::group(
    [
        'prefix' => 'store/customergroups',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'CustomerGroupsController@getAssets')
        ->name('vh.backend.store.api.customergroups.assets');
    /**
     * Get List
     */
    Route::get('/', 'CustomerGroupsController@getList')
        ->name('vh.backend.store.api.customergroups.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'CustomerGroupsController@updateList')
        ->name('vh.backend.store.api.customergroups.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'CustomerGroupsController@deleteList')
        ->name('vh.backend.store.api.customergroups.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'CustomerGroupsController@createItem')
        ->name('vh.backend.store.api.customergroups.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'CustomerGroupsController@getItem')
        ->name('vh.backend.store.api.customergroups.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'CustomerGroupsController@updateItem')
        ->name('vh.backend.store.api.customergroups.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'CustomerGroupsController@deleteItem')
        ->name('vh.backend.store.api.customergroups.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'CustomerGroupsController@listAction')
        ->name('vh.backend.store.api.customergroups.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'CustomerGroupsController@itemAction')
        ->name('vh.backend.store.api.customergroups.item.action');



});
