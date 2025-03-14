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
     * Get Items At Checkout
     */
    Route::get('/{id}/checkout', [CartsController::class, 'getCartItemDetailsAtCheckout'])
        ->name('vh.backend.store.api.carts.read');


    /**
     * delete cart item
     */
    Route::delete('/{uuid}/item/{action}', [CartsController::class, 'deleteCartItem'])
        ->name('vh.backend.store.api.carts.delete.item');

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
     * Add cart item to wishlist
     */
    Route::post('/add-to-wishlist', [CartsController::class, 'addToWishlist'])
        ->name('vh.backend.store.carts.add.to_wishlist');



    /**
     * Generate Cart
     */
    Route::post('/generate', 'ProductsController@generateCart')
        ->name('vh.backend.store.carts.generate.cart');

    /**
     * Get Item by uuid or ID
     */
    Route::get('/{uuid}', [CartsController::class, 'getItem'])
        ->name('vh.backend.store.api.carts.read');

    /**
     * Update items quantity by UUID
     */
    Route::match(['put', 'patch'], '/{uuid}/update', [CartsController::class, 'updateQuantity'])
        ->name('vh.backend.store.api.carts.update.quantity');

    /**
     * Add user to guest cart
     */
    Route::post('/{uuid}/user', [CartsController::class, 'AddUserToCart'])
        ->name('vh.backend.store.api.carts.add.user');

});
