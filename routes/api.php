<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;

// LOGIN-API (public)
Route::post('/login', [AuthController::class, 'login']);

// Protected blog APIs (require login)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/blogs', [BlogController::class, 'store']); // CREATE
    Route::get('/blogs', [BlogController::class, 'index']);  // LIST
    Route::put('/blogs/{id}', [BlogController::class, 'update']); // EDIT
    Route::delete('/blogs/{id}', [BlogController::class, 'destroy']); // DELETE
    Route::post('/blogs/{id}/like-toggle', [BlogController::class, 'toggleLike']); // LIKE TOGGLE
});