<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SchedulingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::get('email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('is.Admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/', [AdminController::class, 'store']);
        Route::get('/{id}', [AdminController::class, 'show']);
        Route::patch('/', [AdminController::class, 'update']);
        Route::delete('/', [AdminController::class, 'destroy']);
    });

    Route::prefix('scheduling')->group(function () {
        Route::get('/', [SchedulingController::class, 'index']);
        Route::post('/', [SchedulingController::class, 'store']);
        Route::get('/{id}', [SchedulingController::class, 'show']);
        Route::patch('/{id}', [SchedulingController::class, 'update']);
        Route::delete('/{id}', [SchedulingController::class, 'destroy']);
    });
});