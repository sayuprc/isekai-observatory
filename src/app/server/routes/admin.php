<?php

declare(strict_types=1);

use AdminUser\Route\AdminUserRouteMap;
use App\Http\Controllers\Api\Admin\V1\AdminUser\ListAdminUserController;
use App\Http\Controllers\Api\Admin\V1\AuditLog\GetAuditLogController;
use App\Http\Controllers\Api\Admin\V1\AuditLog\SearchAuditLogController;
use App\Http\Controllers\Api\Admin\V1\Auth\GenerateRecoveryCodesController;
use App\Http\Controllers\Api\Admin\V1\Auth\LoginFinishController;
use App\Http\Controllers\Api\Admin\V1\Auth\LoginStartController;
use App\Http\Controllers\Api\Admin\V1\Auth\RecoveryFinishController;
use App\Http\Controllers\Api\Admin\V1\Auth\RecoveryStartController;
use App\Http\Controllers\Api\Admin\V1\Auth\RefreshController;
use App\Http\Controllers\Api\Admin\V1\Auth\RegisterFinishController;
use App\Http\Controllers\Api\Admin\V1\Auth\RegisterStartController;
use App\Http\Controllers\Api\Admin\V1\Event\CreateEventController;
use App\Http\Controllers\Api\Admin\V1\Event\DeleteEventController;
use App\Http\Controllers\Api\Admin\V1\Event\GetEventController;
use App\Http\Controllers\Api\Admin\V1\Event\SearchEventController;
use App\Http\Controllers\Api\Admin\V1\Event\UpdateEventController;
use App\Http\Controllers\Api\Admin\V1\Media\CreateMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\DeleteMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\GetMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\SearchMediaController;
use App\Http\Controllers\Api\Admin\V1\Media\UpdateMediaController;
use App\Http\Controllers\Api\Admin\V1\Person\CreatePersonController;
use App\Http\Controllers\Api\Admin\V1\Person\DeletePersonController;
use App\Http\Controllers\Api\Admin\V1\Person\GetPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\ListPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\SearchPersonController;
use App\Http\Controllers\Api\Admin\V1\Person\UpdatePersonController;
use App\Http\Controllers\Api\Admin\V1\Release\CreateReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\DeleteReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\GetReleaseController;
use App\Http\Controllers\Api\Admin\V1\Release\UpdateReleaseController;
use App\Http\Controllers\Api\Admin\V1\ReleaseGroup\CreateReleaseGroupController;
use App\Http\Controllers\Api\Admin\V1\ReleaseGroup\DeleteReleaseGroupController;
use App\Http\Controllers\Api\Admin\V1\ReleaseGroup\GetReleaseGroupController;
use App\Http\Controllers\Api\Admin\V1\ReleaseGroup\SearchReleaseGroupController;
use App\Http\Controllers\Api\Admin\V1\ReleaseGroup\UpdateReleaseGroupController;
use App\Http\Controllers\Api\Admin\V1\Song\CreateSongController;
use App\Http\Controllers\Api\Admin\V1\Song\DeleteSongController;
use App\Http\Controllers\Api\Admin\V1\Song\GetSongController;
use App\Http\Controllers\Api\Admin\V1\Song\SearchSongController;
use App\Http\Controllers\Api\Admin\V1\Song\UpdateSongController;
use App\Http\Controllers\Api\Admin\V1\SongTag\CreateSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\DeleteSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\GetSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\ListSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\SearchSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongTag\UpdateSongTagController;
use App\Http\Controllers\Api\Admin\V1\SongType\ListSongTypeController;
use App\Http\Controllers\Api\Admin\V1\Venue\CreateVenueController;
use App\Http\Controllers\Api\Admin\V1\Venue\DeleteVenueController;
use App\Http\Controllers\Api\Admin\V1\Venue\GetVenueController;
use App\Http\Controllers\Api\Admin\V1\Venue\SearchVenueController;
use App\Http\Controllers\Api\Admin\V1\Venue\UpdateVenueController;
use App\Http\Middleware\Admin\AdminOpenApiValidator;
use App\Http\Middleware\Admin\Authenticate;
use Auth\Route\AuthRouteMap;
use Event\Route\EventRouteMap;
use Illuminate\Support\Facades\Route;
use Media\Route\MediaRouteMap;
use Person\Route\PersonRouteMap;
use Release\Route\ReleaseGroupRouteMap;
use Release\Route\ReleaseRouteMap;
use Song\Route\SongRouteMap;
use Song\Route\SongTypeRouteMap;
use Song\Route\Tag\SongTagRouteMap;
use Support\Route\AuditLogRouteMap;
use Venue\Route\VenueRouteMap;

Route::middleware(AdminOpenApiValidator::class)->group(static function () {
    Route::prefix('admin')->group(static function () {
        Route::prefix('v1')->group(static function () {
            Route::prefix('auth')->group(static function () {
                Route::post('/login/start', [LoginStartController::class, 'handle'])
                    ->middleware('throttle:passkey-login-start')
                    ->name(AuthRouteMap::LoginStart);
                Route::post('/login/finish', [LoginFinishController::class, 'handle'])->name(AuthRouteMap::LoginFinish);
                Route::post('/refresh', [RefreshController::class, 'handle'])
                    ->middleware('throttle:passkey-refresh')
                    ->name(AuthRouteMap::Refresh);
                Route::post('/register/start', [RegisterStartController::class, 'handle'])
                    ->middleware('throttle:passkey-register-start')
                    ->name(AuthRouteMap::RegisterStart);
                Route::post('/register/finish', [RegisterFinishController::class, 'handle'])->name(AuthRouteMap::RegisterFinish);
                Route::post('/recovery/start', [RecoveryStartController::class, 'handle'])
                    ->middleware('throttle:passkey-recovery-start')
                    ->name(AuthRouteMap::RecoveryStart);
                Route::post('/recovery/finish', [RecoveryFinishController::class, 'handle'])->name(AuthRouteMap::RecoveryFinish);
            });

            Route::middleware(Authenticate::class)->group(static function () {
                Route::prefix('admin-users')->group(static function () {
                    Route::get('/', [ListAdminUserController::class, 'handle'])->name(AdminUserRouteMap::List);
                });

                Route::prefix('recovery-codes')->group(static function () {
                    Route::post('/', [GenerateRecoveryCodesController::class, 'handle'])->name(AuthRouteMap::GenerateRecoveryCodes);
                });

                Route::prefix('persons')->group(static function () {
                    Route::post('/', [CreatePersonController::class, 'handle'])->name(PersonRouteMap::Create);
                    Route::get('/', [ListPersonController::class, 'handle'])->name(PersonRouteMap::List);
                    Route::put('/{personId}', [UpdatePersonController::class, 'handle'])->name(PersonRouteMap::Update);
                    Route::delete('/{personId}', [DeletePersonController::class, 'handle'])->name(PersonRouteMap::Delete);
                    Route::get('/search', [SearchPersonController::class, 'handle'])->name(PersonRouteMap::Search);
                    Route::get('/{personId}', [GetPersonController::class, 'handle'])->name(PersonRouteMap::Get);
                });

                Route::prefix('media')->group(static function () {
                    Route::post('/', [CreateMediaController::class, 'handle'])->name(MediaRouteMap::Create);
                    Route::delete('/{mediaId}', [DeleteMediaController::class, 'handle'])->name(MediaRouteMap::Delete);
                    Route::put('/{mediaId}', [UpdateMediaController::class, 'handle'])->name(MediaRouteMap::Update);
                    Route::get('/search', [SearchMediaController::class, 'handle'])->name(MediaRouteMap::Search);
                    Route::get('/{mediaId}', [GetMediaController::class, 'handle'])->name(MediaRouteMap::Get);
                });

                Route::prefix('venues')->group(static function () {
                    Route::post('/', [CreateVenueController::class, 'handle'])->name(VenueRouteMap::Create);
                    Route::put('/{venueId}', [UpdateVenueController::class, 'handle'])->name(VenueRouteMap::Update);
                    Route::delete('/{venueId}', [DeleteVenueController::class, 'handle'])->name(VenueRouteMap::Delete);
                    Route::get('/search', [SearchVenueController::class, 'handle'])->name(VenueRouteMap::Search);
                    Route::get('/{venueId}', [GetVenueController::class, 'handle'])->name(VenueRouteMap::Get);
                });

                Route::prefix('events')->group(static function () {
                    Route::post('/', [CreateEventController::class, 'handle'])->name(EventRouteMap::Create);
                    Route::put('/{eventId}', [UpdateEventController::class, 'handle'])->name(EventRouteMap::Update);
                    Route::delete('/{eventId}', [DeleteEventController::class, 'handle'])->name(EventRouteMap::Delete);
                    Route::get('/search', [SearchEventController::class, 'handle'])->name(EventRouteMap::Search);
                    Route::get('/{eventId}', [GetEventController::class, 'handle'])->name(EventRouteMap::Get);
                });

                Route::prefix('songs')->group(static function () {
                    Route::post('/', [CreateSongController::class, 'handle'])->name(SongRouteMap::Create);
                    Route::put('/{songId}', [UpdateSongController::class, 'handle'])->name(SongRouteMap::Update);
                    Route::delete('/{songId}', [DeleteSongController::class, 'handle'])->name(SongRouteMap::Delete);
                    Route::get('/search', [SearchSongController::class, 'handle'])->name(SongRouteMap::Search);
                    Route::get('/{songId}', [GetSongController::class, 'handle'])->name(SongRouteMap::Get);
                });

                Route::prefix('release-groups')->group(static function () {
                    Route::post('/', [CreateReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::Create);
                    Route::put('/{releaseGroupId}', [UpdateReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::Update);
                    Route::delete('/{releaseGroupId}', [DeleteReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::Delete);
                    Route::get('/search', [SearchReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::Search);
                    Route::get('/{releaseGroupId}', [GetReleaseGroupController::class, 'handle'])->name(ReleaseGroupRouteMap::Get);
                });

                Route::prefix('releases')->group(static function () {
                    Route::post('/', [CreateReleaseController::class, 'handle'])->name(ReleaseRouteMap::Create);
                    Route::put('/{releaseId}', [UpdateReleaseController::class, 'handle'])->name(ReleaseRouteMap::Update);
                    Route::delete('/{releaseId}', [DeleteReleaseController::class, 'handle'])->name(ReleaseRouteMap::Delete);
                    Route::get('/{releaseId}', [GetReleaseController::class, 'handle'])->name(ReleaseRouteMap::Get);
                });

                Route::prefix('song-types')->group(static function () {
                    Route::get('/', [ListSongTypeController::class, 'handle'])->name(SongTypeRouteMap::List);
                });

                Route::prefix('audit-logs')->group(static function () {
                    Route::get('/search', [SearchAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Search);
                    Route::get('/{auditLogId}', [GetAuditLogController::class, 'handle'])->name(AuditLogRouteMap::Get);
                });

                Route::prefix('song-tags')->group(static function () {
                    Route::post('/', [CreateSongTagController::class, 'handle'])->name(SongTagRouteMap::Create);
                    Route::get('/', [ListSongTagController::class, 'handle'])->name(SongTagRouteMap::List);
                    Route::put('/{songTagId}', [UpdateSongTagController::class, 'handle'])->name(SongTagRouteMap::Update);
                    Route::delete('/{songTagId}', [DeleteSongTagController::class, 'handle'])->name(SongTagRouteMap::Delete);
                    Route::get('/search', [SearchSongTagController::class, 'handle'])->name(SongTagRouteMap::Search);
                    Route::get('/{songTagId}', [GetSongTagController::class, 'handle'])->name(SongTagRouteMap::Get);
                });
            });
        });
    });
});
