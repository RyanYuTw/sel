<?php

use App\Http\Controllers\HandbookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::apiResource('handbooks', HandbookController::class);
Route::post('upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload']);
Route::get('zhuyin', [App\Http\Controllers\ZhuyinController::class, 'getZhuyin']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
