<?php

Route::group(
    [
    'prefix' => 'backend/store/brands',
    
    'middleware' => ['web', 'has.backend.access'],
    
    'namespace' => 'Backend',
],
function () {
     //---------------------------------------------------------
    Route::get('/assets', 'BrandsController@getAssets')
        ->name('vh.backend.store.brands.assets');
    //---------------------------------------------------------
    Route::get('/', 'BrandsController@getList')
        ->name('vh.backend.store.brands.list');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/', 'BrandsController@updateList')
        ->name('vh.backend.store.brands.list.updates');
    //---------------------------------------------------------
    Route::delete('/', 'BrandsController@deleteList')
        ->name('vh.backend.store.brands.list.delete');
    //---------------------------------------------------------
    Route::post('/', 'BrandsController@createItem')
        ->name('vh.backend.store.brands.create');
    //---------------------------------------------------------
    Route::get('/{id}', 'BrandsController@getItem')
        ->name('vh.backend.store.brands.read');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/{id}', 'BrandsController@updateItem')
        ->name('vh.backend.store.brands.update');
    //---------------------------------------------------------
    Route::delete('/{id}', 'BrandsController@deleteItem')
        ->name('vh.backend.store.brands.delete');
    //---------------------------------------------------------

});
