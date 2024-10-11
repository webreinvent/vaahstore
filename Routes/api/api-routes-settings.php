<?php

/*
 * API url will be: <base-url>/public/api/store/settings
 */
Route::group(
    [
        'prefix' => 'store/settings',
        'middleware' => ['auth:api'],
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', 'SettingsController@getAssets')
        ->name('vh.backend.store.api.settings.assets');
    /**
     * Get List
     */
    Route::get('/', 'SettingsController@getList')
        ->name('vh.backend.store.api.settings.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'SettingsController@updateList')
        ->name('vh.backend.store.api.settings.list.update');

    /**
     * Create Bulk Records
     */
    Route::any('/fill/bulk/method', 'SettingsController@createBulkRecords')
        ->name('vh.backend.store.settings.create.bulk.records');

    /**
     * Get Crud Records Count
     */
    Route::get('/get/all-item/count', 'SettingsController@getItemsCount')
        ->name('vh.backend.store.settings.get.items.count');

});
