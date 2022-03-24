<?php


Route::group(
    [
    'prefix' => 'store/vendors',
    'namespace' => 'Backend',
    ],
    function () {
        //---------------------------------------------------------
        Route::get('/assets', 'VendorsController@getAssets')
            ->name('vh.backend.store.api.vendors.assets');
        //---------------------------------------------------------
        Route::get('/', 'VendorsController@getList')
            ->name('vh.backend.store.api.vendors.list');
        //---------------------------------------------------------
        Route::post('/{id}', 'VendorsController@createItem')
            ->name('vh.backend.store.api.vendors.create');
        //---------------------------------------------------------
        Route::get('/{id}', 'VendorsController@getItem')
            ->name('vh.backend.store.api.vendors.read');
        //---------------------------------------------------------
        Route::match(['put', 'patch'], '/{id}', 'VendorsController@updateItem')
            ->name('vh.backend.store.api.vendors.update');
        //---------------------------------------------------------
        Route::delete('/{id}', 'VendorsController@deteleItem')
            ->name('vh.backend.store.api.vendors.delete');
        //---------------------------------------------------------
    });
