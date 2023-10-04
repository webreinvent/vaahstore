<?php

Route::group(
    [
        'prefix' => 'backend/store/customergroups',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'CustomerGroupsController@getAssets')
        ->name('vh.backend.store.customergroups.assets');
    /**
     * Get List
     */
    Route::get('/', 'CustomerGroupsController@getList')
        ->name('vh.backend.store.customergroups.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'CustomerGroupsController@updateList')
        ->name('vh.backend.store.customergroups.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'CustomerGroupsController@deleteList')
        ->name('vh.backend.store.customergroups.list.delete');




    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'CustomerGroupsController@fillItem')
        ->name('vh.backend.store.customergroups.fill');

    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'CustomerGroupsController@fillItem')
        ->name('vh.backend.store.customergroups.fill');

    /**
     * Create Item
     */
    Route::post('/', 'CustomerGroupsController@createItem')
        ->name('vh.backend.store.customergroups.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'CustomerGroupsController@getItem')
        ->name('vh.backend.store.customergroups.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'CustomerGroupsController@updateItem')
        ->name('vh.backend.store.customergroups.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'CustomerGroupsController@deleteItem')
        ->name('vh.backend.store.customergroups.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'CustomerGroupsController@listAction')
        ->name('vh.backend.store.customergroups.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'CustomerGroupsController@itemAction')
        ->name('vh.backend.store.customergroups.item.action');

    //---------------------------------------------------------

});
