<?php


Route::group(
    [
        'prefix'     => 'store/stores',
        'namespace' => 'Api',
    ],
    function () {

        /**
         * Get Assets
         */
        Route::get('/assets', 'StoresControllerController@getAssets')
            ->name('vh.backend.store.api.storescontroller.assets');
        /**
         * Get List
         */
        Route::get('/', 'StoresControllerController@getList')
            ->name('vh.backend.store.api.storescontroller.list');
        /**
         * Update List
         */
        Route::match(['put', 'patch'], '/', 'StoresControllerController@updateList')
            ->name('vh.backend.store.api.storescontroller.list.update');
        /**
         * Delete List
         */
        Route::delete('/', 'StoresControllerController@deleteList')
            ->name('vh.backend.store.api.storescontroller.list.delete');


        /**
         * Create Item
         */
        Route::post('/', 'StoresControllerController@createItem')
            ->name('vh.backend.store.api.storescontroller.create');
        /**
         * Get Item
         */
        Route::get('/{id}', 'StoresControllerController@getItem')
            ->name('vh.backend.store.api.storescontroller.read');
        /**
         * Update Item
         */
        Route::match(['put', 'patch'], '/{id}', 'StoresControllerController@updateItem')
            ->name('vh.backend.store.api.storescontroller.update');
        /**
         * Delete Item
         */
        Route::delete('/{id}', 'StoresControllerController@deleteItem')
            ->name('vh.backend.store.api.storescontroller.delete');

        /**
         * List Actions
         */
        Route::any('/action/{action}', 'StoresControllerController@listAction')
            ->name('vh.backend.store.api.storescontroller.list.action');

        /**
         * Item actions
         */
        Route::any('/{id}/action/{action}', 'StoresControllerController@itemAction')
            ->name('vh.backend.store.api.storescontroller.item.action');



    });
