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
         * Attach Products To A Vendor
         */
        Route::post('/{id}/products', 'VendorsController@attachProducts')
            ->name('vh.backend.store.vendors.attach.Product');

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
        /**
         * Attach Users With Roles To A Vendor
         */
        Route::post('/{id}/users/roles', 'VendorsController@attachUsersRoles')
            ->name('vh.backend.store.vendors.createVendorUser');

        Route::post('/remove/user', 'VendorsController@removeVendorUser')
            ->name('vh.backend.store.vendors.remove.vendor.user');


        Route::post('/search/route-query-products', 'VendorsController@setProductInFilter')
            ->name('vh.backend.store.vendors.search.products-using-url-slug');

        Route::any('/get/default/store', 'VendorsController@defaultStore')
            ->name('vh.backend.store.vendors.search.default.store');

        /**
         * Get product count for default vendor
         */
        Route::get('/get/product/count', 'VendorsController@getProductCount')
            ->name('vh.backend.store.vendors.get.product_count');

        /**
         * Get Top Vendors By Sales on Selected Date range
         */
        Route::post('/charts/vendors-by-sales', 'VendorsController@topSellingVendorsData')
            ->name('vh.backend.store.vendors.top_selling_vendors');

        /**
         * Get All Vendors Sales On Selected date range
         */
        Route::post('/charts/sales-by-range', 'VendorsController@vendorSalesByRange')
            ->name('vh.backend.store.vendors.charts.vendor_sales');
    });
