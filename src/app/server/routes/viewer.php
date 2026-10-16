<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Viewer\V1\Event\ListEventController;
use App\Http\Controllers\Api\Viewer\V1\Media\ListMediaController;
use App\Http\Controllers\Api\Viewer\V1\Release\ListReleaseGroupController;
use App\Http\Controllers\Api\Viewer\V1\Song\ListSongController;
use App\Http\Middleware\Viewer\ViewerOpenApiValidator;
use Event\Route\ViewerEventRouteMap;
use Illuminate\Support\Facades\Route;
use Media\Route\ViewerMediaRouteMap;
use Release\Route\ViewerReleaseGroupRouteMap;
use Song\Route\ViewerSongRouteMap;

Route::middleware(ViewerOpenApiValidator::class)->group(static function () {
    Route::prefix('v1')->group(static function () {
        Route::prefix('songs')->group(static function () {
            Route::get('/', [ListSongController::class, 'handle'])->name(ViewerSongRouteMap::List);
        });
        Route::prefix('events')->group(static function () {
            Route::get('/', [ListEventController::class, 'handle'])->name(ViewerEventRouteMap::List);
        });
        Route::prefix('media')->group(static function () {
            Route::get('/', [ListMediaController::class, 'handle'])->name(ViewerMediaRouteMap::List);
        });
        Route::prefix('release-groups')->group(static function () {
            Route::get('/', [ListReleaseGroupController::class, 'handle'])->name(ViewerReleaseGroupRouteMap::List);
        });
    });
});
