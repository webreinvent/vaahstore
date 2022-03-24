<?php

Route::group(
    [
    'prefix' => 'backend/store/vendors',
    
    'middleware' => ['web', 'has.backend.access'],
    
    'namespace' => 'Backend',
],
function () {
     //---------------------------------------------------------
    Route::get('/assets', 'VendorsController@getAssets')
        ->name('vh.backend.store.vendors.assets');
    //---------------------------------------------------------
    Route::get('/', 'VendorsController@getList')
        ->name('vh.backend.store.vendors.list');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/', 'VendorsController@updateList')
        ->name('vh.backend.store.vendors.list.updates');
    //---------------------------------------------------------
    Route::delete('/', 'VendorsController@deleteList')
        ->name('vh.backend.store.vendors.list.delete');
    //---------------------------------------------------------
    Route::post('/', 'VendorsController@createItem')
        ->name('vh.backend.store.vendors.create');
    //---------------------------------------------------------
    Route::get('/{id}', 'VendorsController@getItem')
        ->name('vh.backend.store.vendors.read');
    //---------------------------------------------------------
    Route::match(['put', 'patch'], '/{id}', 'VendorsController@updateItem')
        ->name('vh.backend.store.vendors.update');
    //---------------------------------------------------------
    Route::delete('/{id}', 'VendorsController@deleteItem')
        ->name('vh.backend.store.vendors.delete');
    //---------------------------------------------------------

});
