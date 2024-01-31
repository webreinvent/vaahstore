<?php

Route::group(
    [
        'prefix' => 'backend/store/productmedias',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'ProductMediasController@getAssets')
        ->name('vh.backend.store.productmedias.assets');
    /**
     * Get List
     */
    Route::get('/', 'ProductMediasController@getList')
        ->name('vh.backend.store.productmedias.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'ProductMediasController@updateList')
        ->name('vh.backend.store.productmedias.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'ProductMediasController@deleteList')
        ->name('vh.backend.store.productmedias.list.delete');

    /**
     * POST Upload image
     */
    Route::post('/image/upload', 'ProductMediasController@uploadImage')
        ->name('vh.backend.store.productmedias.list.uploadImage');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'ProductMediasController@fillItem')
        ->name('vh.backend.store.productmedias.fill');


    /**
     * Create Item
     */
    Route::post('/', 'ProductMediasController@createItem')
        ->name('vh.backend.store.productmedias.create');


    /**
     * Get Item
     */
    Route::get('/{id}', 'ProductMediasController@getItem')
        ->name('vh.backend.store.productmedias.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'ProductMediasController@updateItem')
        ->name('vh.backend.store.productmedias.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'ProductMediasController@deleteItem')
        ->name('vh.backend.store.productmedias.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'ProductMediasController@listAction')
        ->name('vh.backend.store.productmedias.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'ProductMediasController@itemAction')
        ->name('vh.backend.store.productmedias.item.action');

    /**
     * Search product
     */
    Route::any('/search/product', 'ProductMediasController@searchProduct')
        ->name('vh.backend.store.productmedias.search.product');



    /**
     * Search status
     */
    Route::any('/search/status', 'ProductMediasController@searchStatus')
        ->name('vh.backend.store.productmedias.search.status');

    //---------------------------------------------------------
    /**
     * Search variation
     */
    Route::post('/filter/search/variations', 'ProductMediasController@searchVariation')
        ->name('vh.backend.store.productmedias.search.filter.variations');

    /**
     * Search variation after refresh
     */
    Route::post('/filter/search/variations-by-slug', 'ProductMediasController@searchVariationsUsingUrlSlug')
        ->name('vh.backend.store.productmedias.search.filtered.variations-by-slug');

    /**
     * Search media
     */
    Route::post('/filter/search/media-type', 'ProductMediasController@searchMediaType')
        ->name('vh.backend.store.productmedias.search.filter.media');

    /**
     * Search media type after refresh
     */
    Route::post('/filter/search/media-type-by-slug', 'ProductMediasController@searchMediaUsingUrlType')
        ->name('vh.backend.store.productmedias.search.filter.media-type-by-slug');

    /**
     * Search variations of a product
     */
    Route::post('/search/product/variations', 'ProductMediasController@searchVariationOfProduct')
        ->name('vh.backend.store.productmedias.search.product.variations');

});
