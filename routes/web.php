<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('role', \App\Http\Controllers\RoleController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('unit', \App\Http\Controllers\UnitController::class);
    Route::resource('permission', \App\Http\Controllers\PermissionController::class);

    // Folders Route
    // Route::prefix('folders')->name('folders.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\FolderController::class, 'index'])->name('index');
    //     Route::get('/tree', [\App\Http\Controllers\FolderController::class, 'getFolderTree'])->name('tree');
    //     Route::post('/', [\App\Http\Controllers\FolderController::class, 'store'])->name('store');
    //     Route::get('/search', [\App\Http\Controllers\FolderController::class, 'search'])->name('search');
    //     Route::get('/statistics', [\App\Http\Controllers\FolderController::class, 'statistics'])->name('statistics');

    //     // Individual folder routes
    //     Route::get('{folder}', [\App\Http\Controllers\FolderController::class, 'show'])->name('show');
    //     Route::put('{folder}', [\App\Http\Controllers\FolderController::class, 'update'])->name('update');
    //     Route::delete('{folder}', [\App\Http\Controllers\FolderController::class, 'destroy'])->name('destroy');
    //     Route::post('{folder}/move', [\App\Http\Controllers\FolderController::class, 'move'])->name('move');

    //     // Permission management routes
    //     Route::get('{folder}/permissions', [\App\Http\Controllers\FolderPermissionController::class, 'show'])->name('permissions');
    //     Route::post('{folder}/permissions/grant', [\App\Http\Controllers\FolderPermissionController::class, 'grant'])->name('permissions.grant');
    //     Route::delete('{folder}/permissions/revoke', [\App\Http\Controllers\FolderPermissionController::class, 'revoke'])->name('permissions.revoke');
    //     Route::get('{folder}/access-preview', [\App\Http\Controllers\FolderController::class, 'accessPreview'])->name('access-preview');
    // });
    Route::get('/folders', [FolderController::class, 'index'])->name('folders.index');

    // AJAX routes for folder operations
    Route::get('/folders/browse/{folder?}', [FolderController::class, 'browse'])->name('folders.browse');
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::get('/folders/search', [FolderController::class, 'search'])->name('folders.search');
    Route::get('/units', [FolderController::class, 'getUnits'])->name('folders.units');

    // Document routes
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/share/{document}', [DocumentController::class, 'share'])->name('documents.share');

    // Arsip Surat Route
    Route::get('/arsip-surat', [\App\Http\Controllers\ArsipSuratController::class, 'index'])->name('arsip-surat.index');


    // Route::prefix('documents')->name('documents.')->group(function () {
    //     Route::get('/', [\App\Http\Controllers\DocumentController::class, 'index'])->name('index');
    //     Route::post('/', [\App\Http\Controllers\DocumentController::class, 'store'])->name('store');
    //     Route::get('{document}', [\App\Http\Controllers\DocumentController::class, 'show'])->name('show');
    //     Route::put('{document}', [\App\Http\Controllers\DocumentController::class, 'update'])->name('update');
    //     Route::delete('{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('destroy');
    //     Route::get('{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('download');
    // });
});

require __DIR__ . '/auth.php';
