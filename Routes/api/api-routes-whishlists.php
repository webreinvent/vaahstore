<?php

/*
 * API url will be: <base-url>/public/api/store/whishlists
 */
Route::group(
    [
        'prefix' => 'store/whishlists',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'WhishlistsController@getAssets')
        ->name('vh.backend.store.api.whishlists.assets');
    /**
     * Get List
     */
    Route::get('/', 'WhishlistsController@getList')
        ->name('vh.backend.store.api.whishlists.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WhishlistsController@updateList')
        ->name('vh.backend.store.api.whishlists.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WhishlistsController@deleteList')
        ->name('vh.backend.store.api.whishlists.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'WhishlistsController@createItem')
        ->name('vh.backend.store.api.whishlists.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WhishlistsController@getItem')
        ->name('vh.backend.store.api.whishlists.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WhishlistsController@updateItem')
        ->name('vh.backend.store.api.whishlists.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WhishlistsController@deleteItem')
        ->name('vh.backend.store.api.whishlists.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WhishlistsController@listAction')
        ->name('vh.backend.store.api.whishlists.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WhishlistsController@itemAction')
        ->name('vh.backend.store.api.whishlists.item.action');



});
