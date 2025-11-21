<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HandbookController;

Route::get('/preview/{handbook}', [HandbookController::class, 'preview'])->name('handbook.preview');

Route::get('/draw', function () {
    return view('draw');
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
