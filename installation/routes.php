<?php

use Illuminate\Support\Facades\Route;
use Installation\Http\Controllers\InstallController;
use Installation\Http\Middleware\AlreadyInstalledMiddleware;

Route::middleware(['web', AlreadyInstalledMiddleware::class])->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('installer.index');
    Route::get('/install/step/{step}', [InstallController::class, 'step'])->name('installer.step');
    Route::post('/install/step/{step}', [InstallController::class, 'processStep'])->name('installer.process');
    Route::match(['get', 'post'], '/install/finalize', [InstallController::class, 'finalize'])->name('installer.finalize');
    Route::get('/install/done', [InstallController::class, 'done'])->name('installer.done');
});
