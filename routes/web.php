<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MainController;


Route::middleware(['guest'])->group(function () {

    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginSubmitForm'])->name('login.submit');

});

/** ====================================================================================
 *  =============================== AUTHENTICATION ROUTES ==============================
 *  ====================================================================================
 */

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [MainController::class, 'index'])->name('dashboard');
    
    Route::get('/queue/create', [MainController::class, 'createQueue'])->name('queue.create');
    Route::post('/queue/create', [MainController::class, 'createQueueSubmit'])->name('queue.create.submit');
    Route::get('/queue/{id}', [MainController::class, 'queueDetails'])->name('queue.details');
    
    Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    Route::post('/change-password', [AuthController::class, 'changePasswordSubmit'])->name('change.password.submit');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
