<?php

use Fixik\LogViewer\Controllers\LogViewerController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('log-viewer.routes.prefix'))
    ->middleware(config('log-viewer.routes.middleware'))
    ->group(function () {
        Route::get('/', [LogViewerController::class, 'index'])->name('log-viewer.index');
        Route::get('/view', [LogViewerController::class, 'show'])->name('log-viewer.show');
        Route::post('/download', [LogViewerController::class, 'downloadFile'])->name('log-viewer.download');
        Route::post('/delete', [LogViewerController::class, 'deleteFile'])->name('log-viewer.delete-file');
        Route::post('/download-directory', [LogViewerController::class, 'downloadFullDirectory'])->name('log-viewer.download-zip');
        Route::delete('/delete-directory', [LogViewerController::class, 'deleteDirectory'])->name('log-viewer.delete-directory');
    });