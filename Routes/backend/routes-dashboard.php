<?php

Route::group(
    [
        'prefix' => 'backend/store/dashboard',

        'middleware' => ['web', 'has.backend.access'],

        'namespace' => 'Backend',
    ],
    function () {
        /**
         * Get Assets
         */
        Route::get('/assets', 'DashboardController@getAssets')
            ->name('vh.backend.store.dashboard.assets');
    });
