<?php

Route::group(
    [
        'prefix' => 'backend/store/brands',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'BrandsController@getAssets')
        ->name('vh.backend.store.brands.assets');
    /**
     * Get List
     */
    Route::get('/', 'BrandsController@getList')
        ->name('vh.backend.store.brands.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'BrandsController@updateList')
        ->name('vh.backend.store.brands.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'BrandsController@deleteList')
        ->name('vh.backend.store.brands.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'BrandsController@fillItem')
        ->name('vh.backend.store.brands.fill');

    /**
     * Create Item
     */
    Route::post('/', 'BrandsController@createItem')
        ->name('vh.backend.store.brands.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'BrandsController@getItem')
        ->name('vh.backend.store.brands.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'BrandsController@updateItem')
        ->name('vh.backend.store.brands.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'BrandsController@deleteItem')
        ->name('vh.backend.store.brands.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'BrandsController@listAction')
        ->name('vh.backend.store.brands.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'BrandsController@itemAction')
        ->name('vh.backend.store.brands.item.action');


    /**
     * Search ApprovedBy
     */
    Route::any('/search/approved/by', 'BrandsController@searchApprovedBy')
        ->name('vh.backend.store.brands.search.approved');

    /**
     * Search Registered By
     */
    Route::any('/search/registered/by', 'BrandsController@searchRegisteredBy')
        ->name('vh.backend.store.brands.search.Registered');

    /**
     * Search Brand Status
     */
    Route::any('/search/brand/status', 'BrandsController@searchBrandStatus')
        ->name('vh.backend.store.brands.search.status');

    Route::post('image/upload', 'BrandsController@imageUploads')
        ->name('vh.backend.store.brands.image.upload');

    //---------------------------------------------------------

});
