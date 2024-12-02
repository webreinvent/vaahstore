<?php

use VaahCms\Modules\Store\Http\Controllers\Backend\ImportController;

Route::group(
    [
        'prefix' => 'backend/store/imports',

        'middleware' => ['web', 'has.backend.access'],

    ],
    function () {

        /**
         * Get Assets
         */
        Route::get('/assets', [ImportController::class, 'getAssets'])
            ->name('vh.backend.store.import.assets');

        /**
         * Import Data
         */
        Route::post('/import/data', [ImportController::class,'importData'])
            ->name('vh.backend.store.import.data');

        /**
         * Get Import Sample File
         */
        Route::get('/get/sample-file', [ImportController::class,'getSampleFile'])
            ->name('vh.backend.store.import.sample-file');



    });
