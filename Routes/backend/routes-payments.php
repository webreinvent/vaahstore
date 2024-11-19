<?php

use VaahCms\Modules\Store\Http\Controllers\Backend\PaymentsController;

Route::group(
    [
        'prefix' => 'backend/store/payments',

        'middleware' => ['web', 'has.backend.access'],

],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [PaymentsController::class, 'getAssets'])
        ->name('vh.backend.store.payments.assets');
    /**
     * Get List
     */
    Route::get('/', [PaymentsController::class, 'getList'])
        ->name('vh.backend.store.payments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [PaymentsController::class, 'updateList'])
        ->name('vh.backend.store.payments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [PaymentsController::class, 'deleteList'])
        ->name('vh.backend.store.payments.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [PaymentsController::class, 'fillItem'])
        ->name('vh.backend.store.payments.fill');

    /**
     * Create Item
     */
    Route::post('/', [PaymentsController::class, 'createItem'])
        ->name('vh.backend.store.payments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [PaymentsController::class, 'getItem'])
        ->name('vh.backend.store.payments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [PaymentsController::class, 'updateItem'])
        ->name('vh.backend.store.payments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [PaymentsController::class, 'deleteItem'])
        ->name('vh.backend.store.payments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [PaymentsController::class, 'listAction'])
        ->name('vh.backend.store.payments.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [PaymentsController::class, 'itemAction'])
        ->name('vh.backend.store.payments.item.action');

    //---------------------------------------------------------
    /**
     * search orders for make payment
     */
    Route::post('/search/orders', [PaymentsController::class, 'searchOrders'])
        ->name('vh.backend.store.payments.search.orders');

    /**
     * get orders for filter
     */
    Route::post('/filter/get/orders',  [PaymentsController::class, 'getOrdersForFilter'])
        ->name('vh.backend.store.payments.get.filter.order');

    /**
     * Search order after refresh in filter
     */
    Route::post('/filter/order-by-user-name', [PaymentsController::class, 'getOrdersByName'])
        ->name('vh.backend.store.payments.search.filter-order-by-user-name');

    Route::post('/charts/payment-methods-pie-chart-data', [PaymentsController::class,'paymentMethodsPieChartData'])
        ->name('vh.backend.store.users.count-chart-data');
});
