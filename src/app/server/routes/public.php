<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Public\V1\Event\ListEventController;
use App\Http\Controllers\Api\Public\V1\Media\ListMediaController;
use App\Http\Controllers\Api\Public\V1\Person\ListPersonController;
use App\Http\Controllers\Api\Public\V1\PersonGroup\ListPersonGroupController;
use App\Http\Controllers\Api\Public\V1\ReleaseGroup\ListReleaseGroupController;
use App\Http\Controllers\Api\Public\V1\Song\ListSongController;
use App\Http\Controllers\Api\Public\V1\Venue\ListVenueController;
use App\Http\Middleware\Public\PublicOpenApiValidator;
use Event\Route\PublicEventRouteMap;
use Illuminate\Support\Facades\Route;
use Media\Route\PublicMediaRouteMap;
use Person\Route\PublicPersonGroupRouteMap;
use Person\Route\PublicPersonRouteMap;
use Release\Route\PublicReleaseGroupRouteMap;
use Song\Route\PublicSongRouteMap;
use Venue\Route\PublicVenueRouteMap;

Route::middleware(PublicOpenApiValidator::class)->group(static function () {
    Route::prefix('public')->group(static function () {
        Route::prefix('v1')->group(static function () {
            Route::prefix('events')->group(static function () {
                Route::get('/', [ListEventController::class, 'handle'])->name(PublicEventRouteMap::List);
            });
            Route::prefix('songs')->group(static function () {
                Route::get('/', [ListSongController::class, 'handle'])->name(PublicSongRouteMap::List);
            });
            Route::prefix('release-groups')->group(static function () {
                Route::get('/', [ListReleaseGroupController::class, 'handle'])->name(PublicReleaseGroupRouteMap::List);
            });
            Route::prefix('media')->group(static function () {
                Route::get('/', [ListMediaController::class, 'handle'])->name(PublicMediaRouteMap::List);
            });
            Route::prefix('people')->group(static function () {
                Route::get('/', [ListPersonController::class, 'handle'])->name(PublicPersonRouteMap::List);
            });
            Route::prefix('person-groups')->group(static function () {
                Route::get('/', [ListPersonGroupController::class, 'handle'])->name(PublicPersonGroupRouteMap::List);
            });
            Route::prefix('venues')->group(static function () {
                Route::get('/', [ListVenueController::class, 'handle'])->name(PublicVenueRouteMap::List);
            });
        });
    });
});
