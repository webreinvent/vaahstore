<?php

Route::group(
    [
        'prefix' => 'backend/store/wishlists',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'WishlistsController@getAssets')
        ->name('vh.backend.store.wishlists.assets');
    /**
     * Get List
     */
    Route::get('/', 'WishlistsController@getList')
        ->name('vh.backend.store.wishlists.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WishlistsController@updateList')
        ->name('vh.backend.store.wishlists.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WishlistsController@deleteList')
        ->name('vh.backend.store.wishlists.list.delete');

    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'WishlistsController@fillItem')
        ->name('vh.backend.store.wishlists.fill');

    /**
     * Create Item
     */
    Route::post('/', 'WishlistsController@createItem')
        ->name('vh.backend.store.wishlists.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WishlistsController@getItem')
        ->name('vh.backend.store.wishlists.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WishlistsController@updateItem')
        ->name('vh.backend.store.wishlists.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WishlistsController@deleteItem')
        ->name('vh.backend.store.wishlists.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WishlistsController@listAction')
        ->name('vh.backend.store.wishlists.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WishlistsController@itemAction')
        ->name('vh.backend.store.wishlists.item.action');


    /**
     * Search vaah users
     */
    Route::any('/search/users', 'WishlistsController@searchVaahUsers')
        ->name('vh.backend.store.wishlists.search.users');

    /**
     * Search type
     */
    Route::any('/search/type', 'WishlistsController@searchType')
        ->name('vh.backend.store.wishlists.search.type');

    /**
     * Search status
     */
    Route::any('/search/status', 'WishlistsController@searchStatus')
        ->name('vh.backend.store.wishlists.search.status');

    //---------------------------------------------------------
    /**
     * Search product
     */
    Route::any('/search/product', 'WishlistsController@searchProduct')
        ->name('vh.backend.store.wishlists.search.product');

    /**
     * Search Products By Slug
     */
    Route::post('/search/products-by-slug', 'WishlistsController@searchProductBySlug')
        ->name('vh.backend.store.wishlists.search.products-by-slug');

    /**
     * Search Users By Slug
     */
    Route::post('/search/users-by-slug', 'WishlistsController@searchUserBySlug')
        ->name('vh.backend.store.wishlists.search.users-by-slug');

});
