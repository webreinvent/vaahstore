<?php

Route::group(
    [
        'prefix' => 'backend/store/products',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductsController@getAssets')
        ->name('vh.backend.store.products.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductsController@getList')
        ->name('vh.backend.store.products.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductsController@updateList')
        ->name('vh.backend.store.products.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductsController@deleteList')
        ->name('vh.backend.store.products.list.delete');

    /**
     * get vendors list
     */
    Route::get('/Vendors_list', 'ProductsController@getVendorsList')
        ->name('vh.backend.store.products.getVendorsList');

    /**
     * create vendor
     */
    Route::post('/vendor', 'ProductsController@createVendor')
        ->name('vh.backend.store.products.vendor');
    /**
     * POST get attribute list
     */
    Route::post('/getAttributeList', 'ProductsController@getAttributeList')
        ->name('vh.backend.store.products.getAttributeList');

    /**
     * POST get attribute values
     */
    Route::post('/getAttributeValue', 'ProductsController@getAttributeValue')
        ->name('vh.backend.store.products.getAttributeValue');

    /**
     * POST create variation
     */
    Route::post('/variation', 'ProductsController@createVariation')
        ->name('vh.backend.store.products.createVariation');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'ProductsController@fillItem')
        ->name('vh.backend.store.products.fill');

    /**
     * Create Item
     */
    Route::post('/', 'ProductsController@createItem')
        ->name('vh.backend.store.products.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductsController@getItem')
        ->name('vh.backend.store.products.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductsController@updateItem')
        ->name('vh.backend.store.products.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductsController@deleteItem')
        ->name('vh.backend.store.products.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductsController@listAction')
        ->name('vh.backend.store.products.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductsController@itemAction')
        ->name('vh.backend.store.products.item.action');

    /**
     * Search Store
     */
    Route::post('/search/store', 'ProductsController@searchStore')
        ->name('vh.backend.store.products.search.store');

    /**
     * Search Brand
     */
    Route::post('/search/brand', 'ProductsController@searchBrand')
        ->name('vh.backend.store.products.search.brand');

    /** Remove vendor
    */
        Route::get('/{id}/remove/vendor', 'ProductsController@removeVendor')
            ->name('vh.backend.store.products.remove.vendor');
    //---------------------------------------------------------

});
