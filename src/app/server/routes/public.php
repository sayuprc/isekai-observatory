<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Public\V1\Event\ListEventController;
use App\Http\Controllers\Api\Public\V1\Song\ListSongController;
use App\Http\Middleware\Public\PublicOpenApiValidator;
use Event\Route\PublicEventRouteMap;
use Illuminate\Support\Facades\Route;
use Song\Route\PublicSongRouteMap;

Route::middleware(PublicOpenApiValidator::class)->group(static function () {
    Route::prefix('public')->group(static function () {
        Route::prefix('v1')->group(static function () {
            Route::prefix('events')->group(static function () {
                Route::get('/', [ListEventController::class, 'handle'])->name(PublicEventRouteMap::List);
            });
            Route::prefix('songs')->group(static function () {
                Route::get('/', [ListSongController::class, 'handle'])->name(PublicSongRouteMap::List);
            });
        });
    });
});
