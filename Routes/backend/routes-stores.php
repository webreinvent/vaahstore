<?php

Route::group(
[
    'prefix' => 'backend/store/stores',
    
    'middleware' => ['web', 'has.backend.access'],
    
    'namespace' => 'Backend',
],
function () {
     //---------------------------------------------------------
     Route::get('/', 'StoresController@getIndex')
    ->name('vh.backend.store.stores.home');
     //---------------------------------------------------------
     Route::any('/assets', 'StoresController@getAssets')
    ->name('vh.backend.store.stores.assets');
     //---------------------------------------------------------
     Route::post('/create', 'StoresController@postCreate')
    ->name('vh.backend.store.stores.create');
     //---------------------------------------------------------
     Route::any('/list', 'StoresController@getList')
    ->name('vh.backend.store.stores.list');
     //---------------------------------------------------------
     Route::any('/item/{uuid}', 'StoresController@getItem')
    ->name('vh.backend.store.stores.item');
     //---------------------------------------------------------
     Route::post('/store/{uuid}', 'StoresController@postStore')
    ->name('vh.backend.store.stores.store');
     //---------------------------------------------------------
     Route::post('/actions/{action_name}', 'StoresController@postActions')
    ->name('vh.backend.store.stores.actions');
     //---------------------------------------------------------
});
