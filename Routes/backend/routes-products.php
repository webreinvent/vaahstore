<?php

Route::group(
    [
    'prefix' => 'backend/store/products',
    
    'middleware' => ['web', 'has.backend.access'],
    
    'namespace' => 'Backend',
],
function () {
     //---------------------------------------------------------
    Route::get('/assets', 'ProductsController@getAssets')
        ->name('vh.backend.store.products.assets');
    //---------------------------------------------------------
    Route::get('/', 'ProductsController@getList')
        ->name('vh.backend.store.products.list');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/', 'ProductsController@updateList')
        ->name('vh.backend.store.products.list.updates');
    //---------------------------------------------------------
    Route::delete('/', 'ProductsController@deleteList')
        ->name('vh.backend.store.products.list.delete');
    //---------------------------------------------------------
    Route::post('/', 'ProductsController@createItem')
        ->name('vh.backend.store.products.create');
    //---------------------------------------------------------
    Route::get('/{id}', 'ProductsController@getItem')
        ->name('vh.backend.store.products.read');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/{id}', 'ProductsController@updateItem')
        ->name('vh.backend.store.products.update');
    //---------------------------------------------------------
    Route::delete('/{id}', 'ProductsController@deleteItem')
        ->name('vh.backend.store.products.delete');
    //---------------------------------------------------------

});
