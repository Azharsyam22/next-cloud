<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\FolderController;
use App\Http\Controllers\Api\V1\QuotaController;
use App\Http\Controllers\Api\V1\ShareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Version 1
Route::prefix('v1')->group(function () {
    // Rute Publik Share API (Tanpa Autentikasi dengan Rate Limiting)
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('public/shares/{token}', [ShareController::class, 'publicShow'])->name('api.public.shares.show');
        Route::get('public/shares/{token}/download', [ShareController::class, 'publicDownload'])->name('api.public.shares.download');
    });

    // Rute Terautentikasi Sanctum dengan Rate Limiting
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Folders API
        Route::get('folders/{folder}/download', [FolderController::class, 'download'])->name('api.folders.download');
        Route::post('folders/{folder}/move', [FolderController::class, 'move'])->name('folders.move');
        Route::apiResource('folders', FolderController::class);

        // Files API
        Route::get('files/{file}/download', [FileController::class, 'download'])->name('api.files.download');
        Route::post('files/{file}/move', [FileController::class, 'move'])->name('api.files.move');
        Route::apiResource('files', FileController::class);

        // Shares API
        Route::apiResource('shares', ShareController::class);

        // Quota API
        Route::get('quota', [QuotaController::class, 'show'])->name('api.quota.show');
        Route::post('quota/recalculate', [QuotaController::class, 'recalculate'])->name('api.quota.recalculate');

        // Admin API
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('stats', [AdminApiController::class, 'stats'])->name('api.admin.stats');
            Route::get('users', [AdminApiController::class, 'users'])->name('api.admin.users');
            Route::post('users/{user}/suspend', [AdminApiController::class, 'toggleSuspend'])->name('api.admin.users.suspend');
            Route::post('users/{user}/quota', [AdminApiController::class, 'overrideQuota'])->name('api.admin.users.quota');
            Route::get('logs', [AdminApiController::class, 'logs'])->name('api.admin.logs');
        });
    });
});
