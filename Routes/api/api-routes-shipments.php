<?php
use VaahCms\Modules\Store\Http\Controllers\Backend\ShipmentsController;
/*
 * API url will be: <base-url>/api/store/shipments
 */
Route::group(
    [
        'prefix' => 'store/shipments',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [ShipmentsController::class, 'getAssets'])
        ->name('vh.backend.store.api.shipments.assets');
    /**
     * Get List
     */
    Route::get('/', [ShipmentsController::class, 'getList'])
        ->name('vh.backend.store.api.shipments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [ShipmentsController::class, 'updateList'])
        ->name('vh.backend.store.api.shipments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [ShipmentsController::class, 'deleteList'])
        ->name('vh.backend.store.api.shipments.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [ShipmentsController::class, 'createItem'])
        ->name('vh.backend.store.api.shipments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [ShipmentsController::class, 'getItem'])
        ->name('vh.backend.store.api.shipments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [ShipmentsController::class, 'updateItem'])
        ->name('vh.backend.store.api.shipments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [ShipmentsController::class, 'deleteItem'])
        ->name('vh.backend.store.api.shipments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [ShipmentsController::class, 'listAction'])
        ->name('vh.backend.store.api.shipments.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [ShipmentsController::class, 'itemAction'])
        ->name('vh.backend.store.api.shipments.item.action');

    /**
     * Get Shipped Order Item List
     */
    Route::get('/{id}/get-shipped-item-list',[ShipmentsController::class, 'getShipmentItemList'])
        ->name('vh.backend.store..api.shipments.get.shipped-item-list');

    /**
     * Update Shipped Item Quantities
     */
    Route::post('/update-shipped-item-quantity', [ShipmentsController::class,'saveEditedShippedQuantity'])
        ->name('vh.backend.store.shipments.save.edited-shipped-quantity');


});
