<?php

Route::group(
[
    'prefix' => 'backend/store/stores',

    'middleware' => ['web', 'has.backend.access'],

    'namespace' => 'Backend',
],
function () {
     //---------------------------------------------------------
    Route::get('/assets', 'StoresController@getAssets')
        ->name('vh.backend.store.stores.assets');
    //---------------------------------------------------------
    Route::get('/', 'StoresController@getList')
        ->name('vh.backend.store.stores.list');
    //---------------------------------------------------------
    Route::post('/{id}', 'StoresController@createItem')
        ->name('vh.backend.store.stores.create');
    //---------------------------------------------------------
    Route::get('/{id}', 'StoresController@getItem')
        ->name('vh.backend.store.stores.read');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/{uuid}', 'StoresController@updateItem')
        ->name('vh.backend.store.stores.update');
    //---------------------------------------------------------
    Route::delete('/{uuid}', 'StoresController@deteleItem')
        ->name('vh.backend.store.stores.delete');
    //---------------------------------------------------------

});
