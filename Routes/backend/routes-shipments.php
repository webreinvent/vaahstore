<?php

use VaahCms\Modules\Store\Http\Controllers\Backend\ShipmentsController;

Route::group(
    [
        'prefix' => 'backend/store/shipments',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [ShipmentsController::class, 'getAssets'])
        ->name('vh.backend.store.shipments.assets');
    /**
     * Get List
     */
    Route::get('/', [ShipmentsController::class, 'getList'])
        ->name('vh.backend.store.shipments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [ShipmentsController::class, 'updateList'])
        ->name('vh.backend.store.shipments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [ShipmentsController::class, 'deleteList'])
        ->name('vh.backend.store.shipments.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [ShipmentsController::class, 'fillItem'])
        ->name('vh.backend.store.shipments.fill');

    /**
     * Create Item
     */
    Route::post('/', [ShipmentsController::class, 'createItem'])
        ->name('vh.backend.store.shipments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [ShipmentsController::class, 'getItem'])
        ->name('vh.backend.store.shipments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [ShipmentsController::class, 'updateItem'])
        ->name('vh.backend.store.shipments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [ShipmentsController::class, 'deleteItem'])
        ->name('vh.backend.store.shipments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [ShipmentsController::class, 'listAction'])
        ->name('vh.backend.store.shipments.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [ShipmentsController::class, 'itemAction'])
        ->name('vh.backend.store.shipments.item.action');

    //---------------------------------------------------------

});
