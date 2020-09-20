<?php

/**
 * Adding Store and Delete Files
 */
Route::post('{entity}/{entityId}/files', 'Api\FileUploadController@store');
Route::get('{entity}/{entityId}/files/{id}', 'Api\FileDownloadController@show');
Route::delete('{entity}/{entityId}/files/{id}', 'Api\FileUploadController@destroy');
Route::post('files', 'Api\FileUploadController@store');
Route::get('files/{id}/{name?}', 'Api\FileDownloadController@show');
Route::delete('files/{id}', 'Api\FileController@destroy');