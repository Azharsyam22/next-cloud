<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SsoController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Depan (Landing Page)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('landing');
})->name('home');

// Rute untuk Pengunjung (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // SSO Sistem Akademik
    Route::get('/auth/sso/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
    Route::get('/auth/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
});

// Rute Pengguna Terautentikasi (Auth)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Verifikasi Email
    Route::get('/email/verify', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [VerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');

    // Ganti Kata Sandi (khusus akun publik, diblokir untuk akun akademik)
    Route::put('/user/password', [AuthController::class, 'changePassword'])
        ->middleware('prevent-academic-password-change')
        ->name('user.password.update');

    // Dashboard Pengguna (File Explorer)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Download Berkas
    Route::get('/files/{file}/download', [\App\Http\Controllers\FileWebController::class, 'download'])
        ->name('files.download');

    // Download Folder sebagai .ZIP
    Route::get('/folders/{folder}/download', [\App\Http\Controllers\FolderWebController::class, 'downloadZip'])
        ->name('folders.download');

    // Sampah (Trash)
    Route::get('/trash', function () {
        return view('trash');
    })->name('trash');

    // Tautan Berbagi Saya
    Route::get('/shares/mine', function () {
        return view('shares.mine');
    })->name('shares.mine');

    // Dibagikan dengan Saya
    Route::get('/shares/with-me', function () {
        return view('shares.with-me');
    })->name('shares.with-me');

    // Manajemen Kuota & Penyimpanan
    Route::get('/storage', function () {
        return view('storage');
    })->name('storage.index');
});

// Rute Publik untuk Berbagi Tautan (Tanpa Login)
Route::prefix('s')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\PublicShareController::class, 'show'])
        ->name('shares.public.show');
    Route::get('/{token}/preview', [\App\Http\Controllers\PublicShareController::class, 'preview'])
        ->name('shares.public.preview');
    Route::get('/{token}/download', [\App\Http\Controllers\PublicShareController::class, 'download'])
        ->name('shares.public.download');
    Route::get('/{token}/files/{file}/download', [\App\Http\Controllers\PublicShareController::class, 'downloadFile'])
        ->name('shares.public.file.download');
});

// Rute Admin Panel (khusus role super-admin dan admin-kampus)
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/users', function () {
        return view('admin.users');
    })->name('admin.users');

    Route::get('/logs', function () {
        return view('admin.logs');
    })->name('admin.logs');
});

