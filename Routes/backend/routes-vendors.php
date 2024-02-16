<?php

Route::group(
    [
        'prefix' => 'backend/store/vendors',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
    ],
    function () {
        /**
         * Get Assets
         */
        Route::get('/assets', 'VendorsController@getAssets')
            ->name('vh.backend.store.vendors.assets');
        /**
         * Get List
         */
        Route::get('/', 'VendorsController@getList')
            ->name('vh.backend.store.vendors.list');
        /**
         * Update List
         */
        Route::match(['put', 'patch'], '/', 'VendorsController@updateList')
            ->name('vh.backend.store.vendors.list.update');
        /**
         * Delete List
         */
        Route::delete('/', 'VendorsController@deleteList')
            ->name('vh.backend.store.vendors.list.delete');


        /**
         * Create Product
         */
        Route::post('/add/product', 'VendorsController@createProduct')
            ->name('vh.backend.store.vendors.createProduct');

        /**
         * Remove All Product
         */
        Route::get('/bulk/product/remove/{id}', 'VendorsController@bulkProductRemove')
            ->name('vh.backend.store.vendors.bulkProductRemove');

        /**
         * Remove Single Product
         */
        Route::get('/single/product/remove/{id}', 'VendorsController@singleProductRemove')
            ->name('vh.backend.store.vendors.singleProductRemove');

        /**
         * Fill Form Inputs
         */
        Route::any('/fill', 'VendorsController@fillItem')
            ->name('vh.backend.store.vendors.fill');

        /**
         * Create Item
         */
        Route::post('/', 'VendorsController@createItem')
            ->name('vh.backend.store.vendors.create');
        /**
         * Get Item
         */
        Route::get('/{id}', 'VendorsController@getItem')
            ->name('vh.backend.store.vendors.read');
        /**
         * Update Item
         */
        Route::match(['put', 'patch'], '/{id}', 'VendorsController@updateItem')
            ->name('vh.backend.store.vendors.update');
        /**
         * Delete Item
         */
        Route::delete('/{id}', 'VendorsController@deleteItem')
            ->name('vh.backend.store.vendors.delete');

        /**
         * List Actions
         */
        Route::any('/action/{action}', 'VendorsController@listAction')
            ->name('vh.backend.store.vendors.list.actions');

        /**
         * Item actions
         */
        Route::any('/{id}/action/{action}', 'VendorsController@itemAction')
            ->name('vh.backend.store.vendors.item.action');

        //---------------------------------------------------------
        /**
         * Search store
         */
        Route::any('/search/store', 'VendorsController@searchStore')
            ->name('vh.backend.store.vendors.search.store');

        /**
         * Search product
         */
        Route::any('/search/product', 'VendorsController@searchProduct')
            ->name('vh.backend.store.vendors.search.product');
        /**
         * Search approved by
         */
        Route::any('/search/approved/by', 'VendorsController@searchApprovedBy')
            ->name('vh.backend.store.vendors.search.approved');

        /**
         * Search owned by
         */
        Route::any('/search/owned/by', 'VendorsController@searchOwnedBy')
            ->name('vh.backend.store.vendors.search.owned');

        /**
         * Search status
         */
        Route::any('/search/status', 'VendorsController@searchStatus')
            ->name('vh.backend.store.vendors.search.status');

        Route::any('/search/vendor/user', 'VendorsController@searchUser')
            ->name('vh.backend.store.vendors.search.role.user');

        Route::post('/add/user', 'VendorsController@createVendorUser')
            ->name('vh.backend.store.vendors.createVendorUser');

        Route::post('/search/route-query-products', 'VendorsController@setProductInFilter')
            ->name('vh.backend.store.vendors.search.products-using-url-slug');

    });
