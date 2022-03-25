<?php


Route::group(
    [
    'prefix' => 'store/brands',
    'namespace' => 'Backend',
    ],
    function () {
        //---------------------------------------------------------
        Route::get('/assets', 'BrandsController@getAssets')
            ->name('vh.backend.store.api.brands.assets');
        //---------------------------------------------------------
        Route::get('/', 'BrandsController@getList')
            ->name('vh.backend.store.api.brands.list');
        //---------------------------------------------------------
        Route::post('/{id}', 'BrandsController@createItem')
            ->name('vh.backend.store.api.brands.create');
        //---------------------------------------------------------
        Route::get('/{id}', 'BrandsController@getItem')
            ->name('vh.backend.store.api.brands.read');
        //---------------------------------------------------------
        Route::match(['put', 'patch'], '/{id}', 'BrandsController@updateItem')
            ->name('vh.backend.store.api.brands.update');
        //---------------------------------------------------------
        Route::delete('/{id}', 'BrandsController@deteleItem')
            ->name('vh.backend.store.api.brands.delete');
        //---------------------------------------------------------
    });
