<?php

use VaahCms\Modules\Store\Http\Controllers\Backend\CategoriesController;

Route::group(
    [
        'prefix' => 'backend/store/categories',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [CategoriesController::class, 'getAssets'])
        ->name('vh.backend.store.categories.assets');
    /**
     * Get List
     */
    Route::get('/', [CategoriesController::class, 'getList'])
        ->name('vh.backend.store.categories.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [CategoriesController::class, 'updateList'])
        ->name('vh.backend.store.categories.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [CategoriesController::class, 'deleteList'])
        ->name('vh.backend.store.categories.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [CategoriesController::class, 'fillItem'])
        ->name('vh.backend.store.categories.fill');

    /**
     * Create Item
     */
    Route::post('/', [CategoriesController::class, 'createItem'])
        ->name('vh.backend.store.categories.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [CategoriesController::class, 'getItem'])
        ->name('vh.backend.store.categories.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [CategoriesController::class, 'updateItem'])
        ->name('vh.backend.store.categories.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [CategoriesController::class, 'deleteItem'])
        ->name('vh.backend.store.categories.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [CategoriesController::class, 'listAction'])
        ->name('vh.backend.store.categories.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [CategoriesController::class, 'itemAction'])
        ->name('vh.backend.store.categories.item.action');

    //---------------------------------------------------------

});
