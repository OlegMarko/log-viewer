<?php

use Fixik\LogViewer\Controllers\LogViewerController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('log-viewer.routes.prefix'))
    ->middleware(config('log-viewer.routes.middleware'))
    ->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('log-viewer.index');
        Route::get('/{filename}', [LogViewerController::class, 'show'])->name('log-viewer.show');
    });