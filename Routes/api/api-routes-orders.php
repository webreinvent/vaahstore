<?php

/*
 * API url will be: <base-url>/api/store/orders
 */
Route::group(
    [
        'prefix' => 'store/orders',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'OrdersController@getAssets')
        ->name('vh.backend.store.api.orders.assets');
    /**
     * Get List
     */
    Route::get('/', 'OrdersController@getList')
        ->name('vh.backend.store.api.orders.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'OrdersController@updateList')
        ->name('vh.backend.store.api.orders.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'OrdersController@deleteList')
        ->name('vh.backend.store.api.orders.list.delete');

    /**
     * Validate Order
     */
    Route::post('/validate', 'CartsController@validateOrderDetails')
        ->name('vh.backend.store.api.orders.create');
    /**
     * Place Order
     */
    Route::post('/', 'CartsController@placeOrder')
        ->name('vh.backend.store.api.orders.create');
    /**
     * Get Item
     */
    Route::get('/{uuid}', 'OrdersController@getItem')
        ->name('vh.backend.store.api.orders.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{uuid}', 'OrdersController@updateItem')
        ->name('vh.backend.store.api.orders.update');
    /**
     * Delete Item
     */
    Route::delete('/{uuid}', 'OrdersController@deleteItem')
        ->name('vh.backend.store.api.orders.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'OrdersController@listAction')
        ->name('vh.backend.store.api.orders.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'OrdersController@itemAction')
        ->name('vh.backend.store.api.orders.item.action');

    /**
     * Get Shipped Order Items
     */
    Route::get('/{id}/shipment-items', 'OrdersController@getShippedOrderItems')
        ->name('vh.backend.store.api.orders.get.order-items');

    /**
     * Retrieve Orders Statuses Pie Chart Data
     */
    Route::get('/charts/data', 'OrdersController@fetchOrdersChartData')
        ->name('vh.backend.store.api.orders.charts.statuses_details');

    /**
     * Retrieve Total Sales Over Specific Period
     */
    Route::get('/charts/total-sales-data', 'OrdersController@fetchSalesChartData')
        ->name('vh.backend.store.api.orders.charts.sales_count');

    /**
     * Retrieve Total payment Recieved Over Specific Period
     */
    Route::get('/charts/order-payments-data', 'OrdersController@fetchOrderPaymentsData')
        ->name('vh.backend.store.api.orders.charts.payments_count');

    /**
     * Retrieve Orders Count Details Over Specific Dates
     */
    Route::get('/charts/orders-count-by-range', 'OrdersController@fetchOrdersCountChartData')
        ->name('vh.backend.store.api.orders.charts.orders_count');

    /**
     * get order details after place an order
     */
    Route::get('/{uuid}/items', 'CartsController@getOrderDetails')
        ->name('vh.backend.store.api.order.items.read');

    /**
     * Download invoice
     */
    Route::get('/{id}/invoice', 'OrdersController@downloadInvoice')
        ->name('vh.backend.store.api.order.invoice.download');
});
