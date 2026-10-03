<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('projects')->controller(ProjectController::class)->middleware('auth:sanctum')->group(function () {
    Route::get('/', 'list');
    Route::get('/options', 'options');
    Route::post('/', 'create');
    Route::get('/{pid}', 'view');
    Route::put('/{pid}', 'update');
    Route::delete('/{pid}', 'delete');
});
