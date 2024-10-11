<?php
use VaahCms\Modules\Store\Http\Controllers\Backend\PaymentsController;
/*
 * API url will be: <base-url>/api/store/payments
 */
Route::group(
    [
        'prefix' => 'store/payments',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [PaymentsController::class, 'getAssets'])
        ->name('vh.backend.store.api.payments.assets');
    /**
     * Get List
     */
    Route::get('/', [PaymentsController::class, 'getList'])
        ->name('vh.backend.store.api.payments.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [PaymentsController::class, 'updateList'])
        ->name('vh.backend.store.api.payments.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [PaymentsController::class, 'deleteList'])
        ->name('vh.backend.store.api.payments.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [PaymentsController::class, 'createItem'])
        ->name('vh.backend.store.api.payments.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [PaymentsController::class, 'getItem'])
        ->name('vh.backend.store.api.payments.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [PaymentsController::class, 'updateItem'])
        ->name('vh.backend.store.api.payments.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [PaymentsController::class, 'deleteItem'])
        ->name('vh.backend.store.api.payments.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [PaymentsController::class, 'listAction'])
        ->name('vh.backend.store.api.payments.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [PaymentsController::class, 'itemAction'])
        ->name('vh.backend.store.api.payments.item.action');

    /**
     * search orders for make payment
     */
    Route::post('/search/orders', [PaymentsController::class, 'searchOrders'])
        ->name('vh.backend.store.payments.search.orders');

});
