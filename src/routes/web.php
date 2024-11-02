<?php

use Fixik\LogViewer\Controllers\LogViewerController;
use Illuminate\Support\Facades\Route;

Route::get('/logs-viewer', [LogViewerController::class, 'index'])->name('logs.index');
Route::get('/logs-viewer/{filename}', [LogViewerController::class, 'show'])->name('logs.show');