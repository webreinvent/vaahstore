<?php


Route::group(
    [
        'prefix'     => 'store/stores',
        'namespace' => 'Api',
    ],
    function () {
        //---------------------------------------------------------
        Route::get('/assets', 'StoresController@getAssets')
            ->name('vh.backend.store.api.stores.assets');
        //---------------------------------------------------------
        Route::get('/', 'StoresController@getList')
            ->name('vh.backend.store.api.stores.list');
        //---------------------------------------------------------
        Route::post('/{id}', 'StoresController@createItem')
            ->name('vh.backend.store.api.stores.create');
        //---------------------------------------------------------
        Route::get('/{id}', 'StoresController@getItem')
            ->name('vh.backend.store.api.stores.read');
        //---------------------------------------------------------
        Route::match(['put', 'patch'], '/{id}', 'StoresController@updateItem')
            ->name('vh.backend.store.api.stores.update');
        //---------------------------------------------------------
        Route::delete('/{id}', 'StoresController@deteleItem')
            ->name('vh.backend.store.api.stores.delete');
        //---------------------------------------------------------
    });
