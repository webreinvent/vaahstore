<?php

/*
 * API url will be: <base-url>/api/store/vendors
 */
Route::group(
    [
        'prefix' => 'store/vendors',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'VendorsController@getAssets')
        ->name('vh.backend.store.api.vendors.assets');
    /**
     * Get List
     */
    Route::get('/', 'VendorsController@getList')
        ->name('vh.backend.store.api.vendors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'VendorsController@updateList')
        ->name('vh.backend.store.api.vendors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'VendorsController@deleteList')
        ->name('vh.backend.store.api.vendors.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'VendorsController@createItem')
        ->name('vh.backend.store.api.vendors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'VendorsController@getItem')
        ->name('vh.backend.store.api.vendors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'VendorsController@updateItem')
        ->name('vh.backend.store.api.vendors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'VendorsController@deleteItem')
        ->name('vh.backend.store.api.vendors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'VendorsController@listAction')
        ->name('vh.backend.store.api.vendors.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'VendorsController@itemAction')
        ->name('vh.backend.store.api.vendors.item.action');

    /**
     * Attach With Product
     */
    Route::post('/add/product', 'VendorsController@createProduct')
        ->name('vh.backend.store.api.vendors.createProduct');

    /**
     * Attach Users With Roles
     */
    Route::post('/add/user', 'VendorsController@createVendorUser')
        ->name('vh.backend.store.vendors.createVendorUser');

    /**
     * Get Top Vendors By Sales on Selected Date range
     */
    Route::post('/charts/vendors-by-sales', 'VendorsController@topSellingVendorsData')
        ->name('vh.backend.store.vendors.top_selling_vendors');

    /**
     * Get All Vendors Sales On Selected date range
     */
    Route::post('/charts/sales-by-range', 'VendorsController@vendorSalesByRange')
        ->name('vh.backend.store.vendors.charts.vendor_sales');

});
