<?php

/*
 * API url will be: <base-url>/api/store/wishlists
 */
Route::group(
    [
        'prefix' => 'store/wishlists',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'WishlistsController@getAssets')
        ->name('vh.backend.store.api.whishlists.assets');
    /**
     * Get List
     */
    Route::get('/', 'WishlistsController@getList')
        ->name('vh.backend.store.api.whishlists.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'WishlistsController@updateList')
        ->name('vh.backend.store.api.whishlists.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'WishlistsController@deleteList')
        ->name('vh.backend.store.api.whishlists.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'WishlistsController@createItem')
        ->name('vh.backend.store.api.whishlists.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'WishlistsController@getItem')
        ->name('vh.backend.store.api.whishlists.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'WishlistsController@updateItem')
        ->name('vh.backend.store.api.whishlists.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'WishlistsController@deleteItem')
        ->name('vh.backend.store.api.whishlists.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'WishlistsController@listAction')
        ->name('vh.backend.store.api.whishlists.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'WishlistsController@itemAction')
        ->name('vh.backend.store.api.whishlists.item.action');


    /**
     * update User-Wishlist Products
     */
    Route::post('/{id}/products', 'WishlistsController@updateUserWishlistProducts')
        ->name('vh.backend.store.wishlists.add-products');

    /**
     * move wishlist to cart
     */
    Route::post('/{id}/move-to-cart', 'WishlistsController@moveWishlistToCart')
        ->name('vh.backend.store.wishlists.add-products');

});
