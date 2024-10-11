<?php

/*
 * API url will be: <base-url>/api/store/products
 */
Route::group(
    [
        'prefix' => 'store/products',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductsController@getAssets')
        ->name('vh.backend.store.api.products.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductsController@getList')
        ->name('vh.backend.store.api.products.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductsController@updateList')
        ->name('vh.backend.store.api.products.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductsController@deleteList')
        ->name('vh.backend.store.api.products.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'ProductsController@createItem')
        ->name('vh.backend.store.api.products.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductsController@getItem')
        ->name('vh.backend.store.api.products.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductsController@updateItem')
        ->name('vh.backend.store.api.products.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductsController@deleteItem')
        ->name('vh.backend.store.api.products.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductsController@listAction')
        ->name('vh.backend.store.api.products.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductsController@itemAction')
        ->name('vh.backend.store.api.products.item.action');

    /**
     * Attach With Vendor
     */
    Route::post('/vendor', 'ProductsController@createVendor')
        ->name('vh.backend.store.api.products.vendor');

    /**
     * Get Vendors List for Item
     */
    Route::get('/get-vendors-list/{id}', 'ProductsController@getVendorsListForPrduct')
        ->name('vh.backend.store.api.products.get.vendors-list');

    /**
     * Action Product Vendor i.e preferred or notpreferred
     */
    Route::patch('/{id}/action-for-vendor/{action}', 'ProductsController@vendorPreferredAction')
        ->name('vh.backend.store.api.products.preferred-vendor');

    /**
     * Retrieve Active Attributes
     */
    Route::post('/getAttributeList', 'ProductsController@getAttributeList')
        ->name('vh.backend.store.api.products.getAttributeList');

    /**
     * Retrieve Active AttributeValues
     */
    Route::post('/getAttributeValue', 'ProductsController@getAttributeValue')
        ->name('vh.backend.store.api.products.getAttributeValue');

    /**
     * Create Variation
     */
    Route::post('/variation', 'ProductsController@createVariation')
        ->name('vh.backend.store.products.createVariation');

    /**
     * Add To Cart
     */
    Route::post('/add/product-to-cart', 'ProductsController@addProductToCart')
        ->name('vh.backend.store.products.save.user-info');

});
