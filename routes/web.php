<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BundlesController;
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


    /**
     * =================================================================================
     * =======================================QUEUES====================================
     * =================================================================================
     */

    Route::get('/queue/create', [MainController::class, 'createQueue'])->name('queue.create');
    Route::post('/queue/create', [MainController::class, 'createQueueSubmit'])->name('queue.create.submit');
    Route::get('/queue/generate-hash', [MainController::class, 'genarateQueueHash'])->name('queue.generate.hash');

    Route::get('/queue/edit/{id}', [MainController::class, 'editQueue'])->name('queue.edit');
    Route::post('/queue/edit', [MainController::class, 'editQueueSubmit'])->name('queue.edit.submit');

    Route::get('queue/clone/{id}', [MainController::class, 'cloneQueue'])->name('queue.clone');
    Route::post('queue/clone/', [MainController::class, 'cloneQueueSubmit'])->name('queue.clone.submit');

    Route::get('queue/delete/{id}', [MainController::class, 'deleteQueue'])->name('queue.delete');
    Route::get('queue/delete/confirm/{id}', [MainController::class, 'deleteQueueConfirm'])->name('queue.delete.confirm');

    Route::get('queue/restore/{id}', [MainController::class, 'restoreQueue'])->name('queue.restore');

    Route::get('/queue/{id}', [MainController::class, 'queueDetails'])->name('queue.details');

    /**
     * =================================================================================
     * =======================================BUNDLES===================================
     * =================================================================================
     */

    Route::get('bundles', [BundlesController::class, 'index'])->name('bundles.home');
    Route::get('bundle/create', [BundlesController::class, 'createBundle'])->name('bundle.create');
    Route::post('bundle/create', [BundlesController::class, 'createBundleSubmit'])->name('bundle.create.submit');
    Route::get('bundles/generate-credential-value/{num_chars}', [BundlesController::class, 'generateCredentialValue'])->name('bundle.generate.credential.value');
    Route::get('bundle/edit/{id}', [BundlesController::class, 'editBundle'])->name('bundle.edit');
    Route::post('bundle/edit/submit', [BundlesController::class, 'editBundleSubmit'])->name('bundle.edit.submit');
    Route::get('bundle/delete/{id}', [BundlesController::class, 'deleteBundle'])->name('bundle.delete');
    Route::get('bundle/delete/confirm/{id}', [BundlesController::class, 'deleteBundleConfirm'])->name('bundle.delete.confirm');
    Route::get('bundle/restore/{id}', [BundlesController::class, 'restoreBundle'])->name('bundle.restore');

    
    /**
     * =================================================================================
     * =======================================USERS=====================================
     * =================================================================================
     */
    Route::get('/change-password', [AuthController::class, 'changePassword'])->name('change.password');
    Route::post('/change-password', [AuthController::class, 'changePasswordSubmit'])->name('change.password.submit');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
