<?php

Route::group(
    [
        'prefix' => 'backend/store/settings',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', 'SettingsController@getAssets')
        ->name('vh.backend.store.settings.assets');
    /**
     * Get List
     */
    Route::get('/', 'SettingsController@getList')
        ->name('vh.backend.store.settings.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', 'SettingsController@updateList')
        ->name('vh.backend.store.settings.list.update');


    Route::any('/fill/bulk/method', 'SettingsController@bulkCreateRecords')
        ->name('vh.backend.store.settings.create.bulk.records');
});
