<?php

use Illuminate\Support\Facades\Route;
use Nitm\Content\Http\Controllers\Api\FileDownloadController;
use Nitm\Content\Http\Controllers\Api\FileUploadController;

/**
 * Adding Store and Delete Files
 */
Route::post('{entity}/{entityId}/files', [FileUploadController::class, 'store']);
Route::get('{entity}/{entityId}/files/{id}', [FileDownloadController::class, 'show']);
Route::delete('{entity}/{entityId}/files/{id}', [FileUploadController::class, 'destroy']);
Route::post('files', [FileUploadController::class, 'store']);
Route::get('files/{id}/{name?}', [FileDownloadController::class, 'show']);
Route::delete('files/{id}', [FileUploadController::class, 'destroy']);