<?php

use App\Http\Controllers\KeywordController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Tareas
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'tasks'], function () {
    Route::get('/index', [TaskController::class, 'index']);

    Route::post('/store', [TaskController::class, 'store']);

    Route::put('/toggle/{id}', [TaskController::class, 'toggle']);
});

/*
|--------------------------------------------------------------------------
| Palabras clave
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'keywords'], function () {
    Route::get('/index', [KeywordController::class, 'index']);
    Route::post('/store', [KeywordController::class, 'store']);
});
