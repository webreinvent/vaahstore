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
     * Attach Vendors to a Product
     */
    Route::post('/{id}/vendors', 'ProductsController@attachVendors')
        ->name('vh.backend.store.products.vendor');

    /**
     * Get Vendors List for Item
     */
    Route::get('/{id}/vendors', 'ProductsController@getVendorsListForPrduct')
        ->name('vh.backend.store.api.products.get.vendors-list');

    /**
     * Action Product Vendor i.e preferred or not-preferred
     */
    Route::patch('/{id}/vendors/{vendor_id}/action', 'ProductsController@vendorPreferredAction')
        ->name('vh.backend.store.api.products.preferred-vendor');

    /**
     * Retrieve Active Attributes
     */
    Route::post('/attributes', 'ProductsController@getAttributeList')
        ->name('vh.backend.store.api.products.getAttributeList');

    /**
     * Retrieve Active AttributeValues
     */
    Route::post('/getAttributeValue', 'ProductsController@getAttributeValue')
        ->name('vh.backend.store.api.products.getAttributeValue');

    /**
     * Generate Variations of a product
     */
    Route::post('/{id}/variations/generate', 'ProductsController@generateVariation')
        ->name('vh.backend.store.products.generate.variations');

    /**
     * Add To Cart
     */
    Route::post('/add/product-to-cart', 'ProductsController@addProductToCart')
        ->name('vh.backend.store.products.save.user-info');

    /**
     * Top Selling products
     */
    Route::post('/charts/top-selling-products', 'ProductsController@topSellingProducts')
        ->name('vh.backend.store.products.charts.top_selling_products');

    /**
     * Top Brands By product sales
     */
    Route::post('/charts/top-selling-brands', 'ProductsController@topSellingBrands')
        ->name('vh.backend.store.products.charts.top_selling_brands');

    /**
     * Top Categories By product sales
     */
    Route::post('/charts/top-selling-categories', 'ProductsController@topSellingCategories')
        ->name('vh.backend.store.products.charts.top_selling_categories');
});
