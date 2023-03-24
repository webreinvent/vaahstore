<?php

Route::group(
    [
        'prefix' => 'backend/store/attributegroups',
        
        'middleware' => ['web', 'has.backend.access'],
        
        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'AttributeGroupsController@getAssets')
        ->name('vh.backend.store.attributegroups.assets');
    /**
     * Get List
     */
    Route::get('/', 'AttributeGroupsController@getList')
        ->name('vh.backend.store.attributegroups.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'AttributeGroupsController@updateList')
        ->name('vh.backend.store.attributegroups.list.update');
    /**
     * Delete List
     */
    Route::delete('/', 'AttributeGroupsController@deleteList')
        ->name('vh.backend.store.attributegroups.list.delete');


    /**
     * Create Item
     */
    Route::post('/', 'AttributeGroupsController@createItem')
        ->name('vh.backend.store.attributegroups.create');
    /**
     * Get Item
     */
    Route::get('/{id}', 'AttributeGroupsController@getItem')
        ->name('vh.backend.store.attributegroups.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', 'AttributeGroupsController@updateItem')
        ->name('vh.backend.store.attributegroups.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', 'AttributeGroupsController@deleteItem')
        ->name('vh.backend.store.attributegroups.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', 'AttributeGroupsController@listAction')
        ->name('vh.backend.store.attributegroups.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', 'AttributeGroupsController@itemAction')
        ->name('vh.backend.store.attributegroups.item.action');

    //---------------------------------------------------------

});
