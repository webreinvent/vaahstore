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
     * Fill Form Inputs
     */
    Route::any('/fill', 'AttributeGroupsController@fillItem')
        ->name('vh.backend.store.attributegroups.fill');

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

    Route::any('/search/active-attributes', 'AttributeGroupsController@searchActiveAttribute')
        ->name('vh.backend.store.attributegroups.search.active-attribute');

    //---------------------------------------------------------
    /**
     * Search Attribute
     */
    Route::post('/search/attribute', 'AttributeGroupsController@searchAttribute')
        ->name('vh.backend.store.attributegroups.search.attributes');
    //---------------------------------------------------------
    Route::post('/search/attributes-using-slug', 'AttributeGroupsController@searchAttributeUsingUrlSlug')
        ->name('vh.backend.store.attributegroups.search.filtered-attributes');
    //---------------------------------------------------------

});
