<?php

Route::group(
    [
        'prefix' => 'backend/store/stores',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'StoresController@getAssets')
        ->name('vh.backend.store.stores.assets');
    /**
     * Get List
     */
    Route::get('/', 'StoresController@getList')
        ->name('vh.backend.store.stores.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'StoresController@updateList')
        ->name('vh.backend.store.stores.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'StoresController@deleteList')
        ->name('vh.backend.store.stores.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', 'StoresController@fillItem')
        ->name('vh.backend.store.stores.fill');

    /**
     * Create Item
     */
    Route::post('/', 'StoresController@createItem')
        ->name('vh.backend.store.stores.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'StoresController@getItem')
        ->name('vh.backend.store.stores.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'StoresController@updateItem')
        ->name('vh.backend.store.stores.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'StoresController@deleteItem')
        ->name('vh.backend.store.stores.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'StoresController@listAction')
        ->name('vh.backend.store.stores.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'StoresController@itemAction')
        ->name('vh.backend.store.stores.item.action');

    //---------------------------------------------------------

    /**
     * Search Currencies
     */
    Route::post('/search/currencies', 'StoresController@searchCurrencies')
        ->name('vh.backend.store.stores.search.currencies');

    //---------------------------------------------------------

    /**
     * Search Languages
     */
    Route::post('/search/languages', 'StoresController@searchLanguages')
        ->name('vh.backend.store.stores.search.languages');

});
