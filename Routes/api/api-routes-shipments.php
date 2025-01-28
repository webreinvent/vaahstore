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
     * Get Shipped Order Items List
     */
    Route::get('/{id}/items',[ShipmentsController::class, 'getShipmentItemList'])
        ->name('vh.backend.store..api.shipments.get.shipped-item-list');

    /**
     * Update Shipped Item Quantities
     */
    Route::post('/update-shipped-item-quantity', [ShipmentsController::class,'saveEditedShippedQuantity'])
        ->name('vh.backend.store.shipments.save.edited-shipped-quantity');

    /**
     * Search/get Orders with the Quantities Records
     */
    //---------------------------------------------------------
    Route::post('/search/orders', [ShipmentsController::class, 'searchOrders'])
        ->name('vh.backend.store.shipments.search.orders');

    /**
     * Fetch Shipment Orders Details Count By date range
     */
    Route::post('/charts/orders-shipments-by-range', [ShipmentsController::class,'ordersShipmentByDateRange'])
        ->name('vh.backend.store.shipments.charts.order_details_counts');

    /**
     * Fetch Shipment Items Details Count By date range
     */
    Route::post('/charts/shipment-items-by-range', [ShipmentsController::class,'ordersShipmentItemsByDateRange'])
        ->name('vh.backend.store.shipments.charts.items_details_counts');

    /**
     * Fetch Shipment Items by status By date range
     */
    Route::post('/charts/shipment-items-by-status', [ShipmentsController::class,'shipmentItemsByStatusBarChart'])
        ->name('vh.backend.store.shipments.charts.items_status_details');
});
