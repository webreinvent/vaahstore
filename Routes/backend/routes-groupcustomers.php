<?php

Route::group(
    [
        'prefix' => 'backend/store/groupcustomers',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'GroupCustomersController@getAssets')
        ->name('vh.backend.store.groupcustomers.assets');
    /**
     * Get List
     */
    Route::get('/', 'GroupCustomersController@getList')
        ->name('vh.backend.store.groupcustomers.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'GroupCustomersController@updateList')
        ->name('vh.backend.store.groupcustomers.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'GroupCustomersController@deleteList')
        ->name('vh.backend.store.groupcustomers.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'GroupCustomersController@fillItem')
        ->name('vh.backend.store.groupcustomers.fill');

    /**
     * Create Item
     */
    Route::post('/', 'GroupCustomersController@createItem')
        ->name('vh.backend.store.groupcustomers.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'GroupCustomersController@getItem')
        ->name('vh.backend.store.groupcustomers.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'GroupCustomersController@updateItem')
        ->name('vh.backend.store.groupcustomers.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'GroupCustomersController@deleteItem')
        ->name('vh.backend.store.groupcustomers.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'GroupCustomersController@listAction')
        ->name('vh.backend.store.groupcustomers.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'GroupCustomersController@itemAction')
        ->name('vh.backend.store.groupcustomers.item.action');

    //---------------------------------------------------------

});
