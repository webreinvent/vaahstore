<?php

/*
 * API url will be: <base-url>/public/api/store/settings
 */
Route::group(
    [
        'prefix' => 'store/settings',
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



});
