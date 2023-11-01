<?php

Route::group(
    [
        'prefix' => 'backend/store/orders',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'OrdersController@getAssets')
        ->name('vh.backend.store.orders.assets');
    /**
     * Get List
     */
    Route::get('/', 'OrdersController@getList')
        ->name('vh.backend.store.orders.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'OrdersController@updateList')
        ->name('vh.backend.store.orders.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'OrdersController@deleteList')
        ->name('vh.backend.store.orders.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'OrdersController@fillItem')
        ->name('vh.backend.store.orders.fill');

    /**
     * Create Item
     */
    Route::post('/', 'OrdersController@createItem')
        ->name('vh.backend.store.orders.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'OrdersController@getItem')
        ->name('vh.backend.store.orders.read');

    /**
     * POST create orderItem
     */
    Route::post('/items', 'OrdersController@createOrderItems')
        ->name('vh.backend.store.products.createOrderItems');

    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'OrdersController@updateItem')
        ->name('vh.backend.store.orders.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'OrdersController@deleteItem')
        ->name('vh.backend.store.orders.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'OrdersController@listAction')
        ->name('vh.backend.store.orders.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'OrdersController@itemAction')
        ->name('vh.backend.store.orders.item.action');

    //---------------------------------------------------------

    /**
     * Search Products
     */

    Route::post('/search/products', 'OrdersController@searchProducts')
        ->name('vh.backend.store.orders.search.products');

    //---------------------------------------------------------

    /**
     * Search Product Variations
     */

    Route::post('/search/product-variations', 'OrdersController@searchProductVariations')
        ->name('vh.backend.store.orders.search.product-variations');

    //---------------------------------------------------------

    /**
     * Search Vendor
     */

    Route::post('/search/vendor', 'OrdersController@searchVendor')
        ->name('vh.backend.store.orders.search.vendor');

});
