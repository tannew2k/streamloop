<?php

use App\Http\Controllers\Admin\ChannelCrudController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('user', 'UserCrudController');
    Route::crud('channel', 'ChannelCrudController');
    Route::get('channel/{id}/start', [ChannelCrudController::class, 'startLiveStream']);
    Route::get('channel/{id}/stop', [ChannelCrudController::class, 'stopLiveStream']);
    Route::get('channel/{id}/generate', [ChannelCrudController::class, 'generateIds']);
}); // this should be the absolute last line of this file
