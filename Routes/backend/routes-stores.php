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
    Route::match(['put', 'patch'], '/', 'StoresController@updateList')
        ->name('vh.backend.store.stores.list.updates');
    //---------------------------------------------------------
    Route::delete('/', 'StoresController@deleteList')
        ->name('vh.backend.store.stores.list.delete');
    //---------------------------------------------------------
    Route::post('/', 'StoresController@createItem')
        ->name('vh.backend.store.stores.create');
    //---------------------------------------------------------
    Route::get('/{id}', 'StoresController@getItem')
        ->name('vh.backend.store.stores.read');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/{id}', 'StoresController@updateItem')
        ->name('vh.backend.store.stores.update');
    //---------------------------------------------------------
    Route::delete('/{id}', 'StoresController@deleteItem')
        ->name('vh.backend.store.stores.delete');
    //---------------------------------------------------------

});
