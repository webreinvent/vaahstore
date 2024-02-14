<?php

Route::group(
    [
        'prefix' => 'backend/store/users',

                'middleware' => ['web', 'has.backend.access'],

                'namespace' => 'Backend',
    ],
    function () {
        /**
         * Get Assets
         */
        Route::get('/assets', 'UsersController@getAssets')
            ->name('vh.backend.store.users.assets');
        /**
         * Get List
         */
        Route::get('/', 'UsersController@getList')
            ->name('vh.backend.store.users.list');
        /**
         * Update List
         */
        Route::match(['put', 'patch'], '/', 'UsersController@updateList')
            ->name('vh.backend.store.users.list.update');
        /**
         * Delete List
         */
        Route::delete('/', 'UsersController@deleteList')
            ->name('vh.backend.store.users.list.delete');


        /**
         * Create Item
         */
        Route::post('/', 'UsersController@createItem')
            ->name('vh.backend.store.users.create');
        /**
         * Get Item
         */
        Route::get('/{id}', 'UsersController@getItem')
            ->name('vh.backend.store.users.read');
        /**
         * Update Item
         */
        Route::match(['put', 'patch'], '/{id}', 'UsersController@updateItem')
            ->name('vh.backend.store.users.update');
        /**
         * Delete Item
         */
        Route::delete('/{id}', 'UsersController@deleteItem')
            ->name('vh.backend.store.users.delete');

        /**
         * List Actions
         */
        Route::any('/action/{action}', 'UsersController@listAction')
            ->name('vh.backend.store.users.list.actions');

        /**
         * Item actions
         */
        Route::any('/{id}/action/{action}', 'UsersController@itemAction')
            ->name('vh.backend.store.users.item.action');

        //---------------------------------------------------------

        //---------------------------------------------------------
        Route::get('/item/{id}/roles', 'UsersController@getItemRoles')
            ->name('vh.backend.store.users.role');

        Route::post('/actions/{action_name}', 'UsersController@postActions')
            ->name('vh.backend.store.users.actions');
        //---------------------------------------------------------
        Route::post('/avatar/store', 'UsersController@storeAvatar')
            ->name('vh.backend.store.users.avatar.store');
        //---------------------------------------------------------
        Route::post('/avatar/remove', 'UsersController@removeAvatar')
            ->name('vh.backend.store.users.avatar.remove');
        //---------------------------------------------------------
        /**
         * Search Customer Group filter
         */
        Route::post('/search/customergroup', 'UsersController@searchCustomerGroups')
            ->name('vh.backend.store.users.search.filter.customergroup');
        /**
         * Search Customer Group filter After Refresh
         */
        Route::post('/search/customer-group-by-slug', 'UsersController@getCustomerGroupsBySlug')
            ->name('vh.backend.store.users.search.filter-customergroup-by-slug');

        Route::any('/fill', 'UsersController@fillItem')
            ->name('vh.backend.store.users.fill');
    });
