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
     * Search product variation
     */
    Route::any('/search/product/variation', 'ProductMediasController@searchProductVariation')
        ->name('vh.backend.store.productmedias.search.variation');

    //---------------------------------------------------------

});
