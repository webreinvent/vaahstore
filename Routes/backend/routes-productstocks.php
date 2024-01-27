<?php

Route::group(
    [
        'prefix' => 'backend/store/productstocks',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductStocksController@getAssets')
        ->name('vh.backend.store.productstocks.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductStocksController@getList')
        ->name('vh.backend.store.productstocks.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductStocksController@updateList')
        ->name('vh.backend.store.productstocks.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductStocksController@deleteList')
        ->name('vh.backend.store.productstocks.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'ProductStocksController@fillItem')
        ->name('vh.backend.store.productstocks.fill');

    /**
     * Create Item
     */
    Route::post('/', 'ProductStocksController@createItem')
        ->name('vh.backend.store.productstocks.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductStocksController@getItem')
        ->name('vh.backend.store.productstocks.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductStocksController@updateItem')
        ->name('vh.backend.store.productstocks.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductStocksController@deleteItem')
        ->name('vh.backend.store.productstocks.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductStocksController@listAction')
        ->name('vh.backend.store.productstocks.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductStocksController@itemAction')
        ->name('vh.backend.store.productstocks.item.action');

    /**
     * Search vendor
     */
    Route::any('/search/vendor', 'ProductStocksController@searchVendor')
        ->name('vh.backend.store.productstocks.search.vendor');

    /**
     * Search product
     */
    Route::any('/search/product', 'ProductStocksController@searchProduct')
        ->name('vh.backend.store.productstocks.search.product');

    /**
     * Search variation
     */
    Route::any('/search/product/variation', 'ProductStocksController@searchProductVariation')
        ->name('vh.backend.store.productstocks.search.variation');

    /**
     * Search Filter Selected Product Variation
     */
    Route::any('/search/filter-selected/variation', 'ProductStocksController@searchFilterSelectedProductVariation')
        ->name('vh.backend.store.productstocks.search.filter-selected-variation');

    /**
     * Search warehouse
     */
    Route::any('/search/warehouse', 'ProductStocksController@searchWarehouse')
        ->name('vh.backend.store.productstocks.search.warehouse');

    /**
     * Search Filter Selected Warehouse
     */
    Route::any('/search/filter-selected/warehouse', 'ProductStocksController@searchFilterSelectedWarehouse')
        ->name('vh.backend.store.productstocks.search.filter-selected-warehouse');


    //---------------------------------------------------------
    /**
     * Search Vendors using Slug
     */
    Route::post('/search/vendors-using-url-slug', 'ProductStocksController@searchVendorUsingUrlSlug')
        ->name('vh.backend.store.productstocks.search.vendors-using-url-slug');

    //---------------------------------------------------------

    /**
     * Search Products using Slug
     */
    Route::post('/search/products-using-url-slug', 'ProductStocksController@searchProductUsingUrlSlug')
        ->name('vh.backend.store.productstocks.search.products-using-url-slug');

    //---------------------------------------------------------

    /**
     * Search Variations using Slug
     */
    Route::post('/search/variations-using-url-slug', 'ProductStocksController@searchVariationUsingUrlSlug')
        ->name('vh.backend.store.productstocks.search.variations-using-url-slug');

    //---------------------------------------------------------

    /**
     * Search Warehouses using Slug
     */

    Route::post('/search/warehouses-using-url-slug', 'ProductStocksController@searchWarehouseUsingUrlSlug')
        ->name('vh.backend.store.productstocks.search.warehouses-using-url-slug');


});
