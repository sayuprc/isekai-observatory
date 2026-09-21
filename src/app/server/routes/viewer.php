<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Viewer\V1\Media\ListMediaController;
use App\Http\Controllers\Api\Viewer\V1\Release\ListReleaseGroupController;
use App\Http\Controllers\Api\Viewer\V1\SiteStats\GetSiteStatsController;
use App\Http\Controllers\Api\Viewer\V1\Song\ListSongController;
use App\Http\Middleware\Viewer\ViewerOpenApiValidator;
use Illuminate\Support\Facades\Route;
use Media\Route\ViewerMediaRouteMap;
use Release\Route\ViewerReleaseGroupRouteMap;
use SiteStats\Route\ViewerSiteStatsRouteMap;
use Song\Route\ViewerSongRouteMap;

Route::middleware(ViewerOpenApiValidator::class)->group(static function () {
    Route::prefix('v1')->group(static function () {
        Route::prefix('songs')->group(static function () {
            Route::get('/', [ListSongController::class, 'handle'])->name(ViewerSongRouteMap::List);
        });
        Route::prefix('media')->group(static function () {
            Route::get('/', [ListMediaController::class, 'handle'])->name(ViewerMediaRouteMap::List);
        });
        Route::prefix('release-groups')->group(static function () {
            Route::get('/', [ListReleaseGroupController::class, 'handle'])->name(ViewerReleaseGroupRouteMap::List);
        });
        Route::get('/site-stats', [GetSiteStatsController::class, 'handle'])->name(ViewerSiteStatsRouteMap::Get);
    });
});
