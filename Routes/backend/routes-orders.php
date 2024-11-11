<?php

Route::group(
    [
        'prefix' => 'backend/store/orders',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'OrdersController@getAssets')
        ->name('vh.backend.store.orders.assets');
    /**
     * Get List
     */
    Route::get('/', 'OrdersController@getList')
        ->name('vh.backend.store.orders.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'OrdersController@updateList')
        ->name('vh.backend.store.orders.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'OrdersController@deleteList')
        ->name('vh.backend.store.orders.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'OrdersController@fillItem')
        ->name('vh.backend.store.orders.fill');

    /**
     * Create Item
     */
    Route::post('/', 'OrdersController@createItem')
        ->name('vh.backend.store.orders.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'OrdersController@getItem')
        ->name('vh.backend.store.orders.read');



    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'OrdersController@updateItem')
        ->name('vh.backend.store.orders.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'OrdersController@deleteItem')
        ->name('vh.backend.store.orders.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'OrdersController@listAction')
        ->name('vh.backend.store.orders.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'OrdersController@itemAction')
        ->name('vh.backend.store.orders.item.action');

    //---------------------------------------------------------
    Route::get('/get-order-items/{id}', 'OrdersController@getShippedOrderItems')
        ->name('vh.backend.store.orders.get.order-items');

    Route::post('/charts/data', 'OrdersController@fetchOrdersChartData')
        ->name('vh.backend.store.users.count-chart-data');


    Route::post('/charts/total-sales-data', 'OrdersController@fetchSalesChartData')
        ->name('vh.backend.store.users.count-chart-data');

    Route::post('/charts/order-payments-data', 'OrdersController@fetchOrderPaymentsData')
        ->name('vh.backend.store.users.count-chart-data');

    Route::post('/charts/orders-count-by-range', 'OrdersController@fetchOrdersCountChartData')
        ->name('vh.backend.store.users.count-chart-data');

});
