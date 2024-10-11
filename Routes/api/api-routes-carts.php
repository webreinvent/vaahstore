<?php
use VaahCms\Modules\Store\Http\Controllers\Backend\CartsController;
/*
 * API url will be: <base-url>/api/store/carts
 */
Route::group(
    [
        'prefix' => 'store/carts',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [CartsController::class, 'getAssets'])
        ->name('vh.backend.store.api.carts.assets');
    /**
     * Get List
     */
    Route::get('/', [CartsController::class, 'getList'])
        ->name('vh.backend.store.api.carts.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [CartsController::class, 'updateList'])
        ->name('vh.backend.store.api.carts.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [CartsController::class, 'deleteList'])
        ->name('vh.backend.store.api.carts.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [CartsController::class, 'createItem'])
        ->name('vh.backend.store.api.carts.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [CartsController::class, 'getItem'])
        ->name('vh.backend.store.api.carts.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [CartsController::class, 'updateItem'])
        ->name('vh.backend.store.api.carts.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [CartsController::class, 'deleteItem'])
        ->name('vh.backend.store.api.carts.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [CartsController::class, 'listAction'])
        ->name('vh.backend.store.api.carts.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [CartsController::class, 'itemAction'])
        ->name('vh.backend.store.api.carts.item.action');

    /**
     * cart checkout
     */
    Route::get('/cart-check-out/{id}', [CartsController::class, 'getCartItemDetailsAtCheckout'])
        ->name('vh.backend.store.api.carts.read');

    /**
     * update cart item quantity
     */
    Route::post('/update/quantity', [CartsController::class, 'updateQuantity'])
        ->name('vh.backend.store.carts.update.quantity');

    /**
     * delete cart item
     */
    Route::post('/delete-cart-item', [CartsController::class, 'deleteCartItem'])
        ->name('vh.backend.store.carts.delete.item');

    /**
     * save cart-user address
     */
    Route::post('/save/cart-user-address', [CartsController::class, 'saveCartUserAddress'])
        ->name('vh.backend.store.carts.save.adress');

    /**
     * Delete cart-user Address
     */
    Route::post('/remove/cart-user-address', [CartsController::class, 'removeCartUserAddress'])
        ->name('vh.backend.store.carts.save.address');

    /**
     * Update cart-user shipping address
     */
    Route::post('/update/user-shipping-address', [CartsController::class, 'updateUserShippingAddress'])
        ->name('vh.backend.store.carts.update.address');


    /**
     * Place Order
     */
    Route::post('/place-order', [CartsController::class, 'placeOrder'])
        ->name('vh.backend.store.carts.update.address');

    /**
     * Add cart item to wishlist
     */
    Route::post('/add-to-wishlist', [CartsController::class, 'addToWishlist'])
        ->name('vh.backend.store.carts.add.to_wishlist');

    /**
     * get order details after place an order
     */
    Route::get('/get-order-details/{order_id}', [CartsController::class, 'getOrderDetails'])
        ->name('vh.backend.store.carts.read');

});
