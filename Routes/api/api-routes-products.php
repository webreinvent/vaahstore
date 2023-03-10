<?php


Route::group(
    [
    'prefix' => 'store/products',
    'namespace' => 'Backend',
    ],
    function () {
        //---------------------------------------------------------
        Route::get('/assets', 'ProductsController@getAssets')
            ->name('vh.backend.store.api.products.assets');
        //---------------------------------------------------------
        Route::get('/', 'ProductsController@getList')
            ->name('vh.backend.store.api.products.list');
        //---------------------------------------------------------
        Route::post('/{id}', 'ProductsController@createItem')
            ->name('vh.backend.store.api.products.create');
        //---------------------------------------------------------
        Route::get('/{id}', 'ProductsController@getItem')
            ->name('vh.backend.store.api.products.read');
        //---------------------------------------------------------
        Route::match(['put', 'patch'], '/{id}', 'ProductsController@updateItem')
            ->name('vh.backend.store.api.products.update');
        //---------------------------------------------------------
        Route::delete('/{id}', 'ProductsController@deteleItem')
            ->name('vh.backend.store.api.products.delete');
        //---------------------------------------------------------
    });
