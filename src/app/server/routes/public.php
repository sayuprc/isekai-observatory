<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Public\V1\Event\ListEventController;
use App\Http\Controllers\Api\Public\V1\Media\ListMediaController;
use App\Http\Controllers\Api\Public\V1\Person\ListPersonController;
use App\Http\Controllers\Api\Public\V1\PersonGroup\ListPersonGroupController;
use App\Http\Controllers\Api\Public\V1\ReleaseGroup\ListReleaseGroupController;
use App\Http\Controllers\Api\Public\V1\Song\ListSongController;
use App\Http\Controllers\Api\Public\V1\Tag\ListTagController;
use App\Http\Controllers\Api\Public\V1\Venue\ListVenueController;
use App\Http\Middleware\Public\PublicOpenApiValidator;
use Illuminate\Support\Facades\Route;
use Public\Route\EventRouteMap;
use Public\Route\MediaRouteMap;
use Public\Route\PersonGroupRouteMap;
use Public\Route\PersonRouteMap;
use Public\Route\ReleaseGroupRouteMap;
use Public\Route\SongRouteMap;
use Public\Route\TagRouteMap;
use Public\Route\VenueRouteMap;

Route::middleware(PublicOpenApiValidator::class)->group(static function () {
    Route::prefix('public')->group(static function () {
        Route::prefix('v1')->group(static function () {
            Route::prefix('events')->group(static function () {
                Route::get('/', [ListEventController::class, 'handle'])->name(EventRouteMap::List);
            });
            Route::prefix('songs')->group(static function () {
                Route::get('/', [ListSongController::class, 'handle'])->name(SongRouteMap::List);
            });
            Route::prefix('release-groups')->group(static function () {
                Route::get('/', [ListReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::List);
            });
            Route::prefix('media')->group(static function () {
                Route::get('/', [ListMediaController::class, 'handle'])->name(MediaRouteMap::List);
            });
            Route::prefix('people')->group(static function () {
                Route::get('/', [ListPersonController::class, 'handle'])->name(PersonRouteMap::List);
            });
            Route::prefix('person-groups')->group(static function () {
                Route::get('/', [ListPersonGroupController::class, 'handle'])->name(PersonGroupRouteMap::List);
            });
            Route::prefix('tags')->group(static function () {
                Route::get('/', [ListTagController::class, 'handle'])->name(TagRouteMap::List);
            });
            Route::prefix('venues')->group(static function () {
                Route::get('/', [ListVenueController::class, 'handle'])->name(VenueRouteMap::List);
            });
        });
    });
});
