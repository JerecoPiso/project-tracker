<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->controller(UserController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/register', 'register');
    Route::get('/list', 'list');
    Route::post('/logout', 'logout');
    Route::put('/{pid}', 'update');
    Route::get('/{pid}', 'view');
    Route::delete('/{pid}', 'delete');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
