<?php

Route::group(
    [
        'prefix' => 'backend/store/whishlists',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'WhishlistsController@getAssets')
        ->name('vh.backend.store.whishlists.assets');
    /**
     * Get List
     */
    Route::get('/', 'WhishlistsController@getList')
        ->name('vh.backend.store.whishlists.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WhishlistsController@updateList')
        ->name('vh.backend.store.whishlists.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WhishlistsController@deleteList')
        ->name('vh.backend.store.whishlists.list.delete');

    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'WhishlistsController@fillItem')
        ->name('vh.backend.store.whishlists.fill');

    /**
     * Create Item
     */
    Route::post('/', 'WhishlistsController@createItem')
        ->name('vh.backend.store.whishlists.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WhishlistsController@getItem')
        ->name('vh.backend.store.whishlists.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WhishlistsController@updateItem')
        ->name('vh.backend.store.whishlists.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WhishlistsController@deleteItem')
        ->name('vh.backend.store.whishlists.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WhishlistsController@listAction')
        ->name('vh.backend.store.whishlists.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WhishlistsController@itemAction')
        ->name('vh.backend.store.whishlists.item.action');


    /**
     * Search vaah users
     */
    Route::any('/search/users', 'WhishlistsController@searchVaahUsers')
        ->name('vh.backend.store.whishlists.search.users');

    /**
     * Search type
     */
    Route::any('/search/type', 'WhishlistsController@searchType')
        ->name('vh.backend.store.whishlists.search.type');

    /**
     * Search status
     */
    Route::any('/search/status', 'WhishlistsController@searchStatus')
        ->name('vh.backend.store.whishlists.search.status');

    //---------------------------------------------------------
    /**
     * Search product
     */
    Route::any('/search/product', 'WhishlistsController@searchProduct')
        ->name('vh.backend.store.whishlists.search.product');

    /**
     * Search Products By Slug
     */
    Route::post('/search/products-by-slug', 'WhishlistsController@searchProductBySlug')
        ->name('vh.backend.store.whishlists.search.products-by-slug');

    /**
     * Search Users By Slug
     */
    Route::post('/search/users-by-slug', 'WhishlistsController@searchUserBySlug')
        ->name('vh.backend.store.whishlists.search.users-by-slug');

});
