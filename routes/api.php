<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlantController;
use App\Http\Controllers\UserController;

// Public Routes (No Authentication Required)
Route::get("/sample", function () {
    return response()->json([
        "message" => "This is a sample API endpoint.",
        "token" => "xxxxx",
        "user" => [
            "name" => "Juan",
            "age" => 19
        ]
    ]);
});

Route::post("/login", [AuthController::class, "login"]);

// Protected Routes (Authentication Required)
Route::middleware('auth:sanctum')->group(function () {
    
    // User Routes
    Route::get("/home", [UserController::class, "index"]);
    Route::post("/new-record", [UserController::class, "store"]);
    Route::apiResource('users', UserController::class);
    
    // Plant Routes
    Route::apiResource('plants', PlantController::class);
    Route::get('plants/status/{status}', [PlantController::class, 'getByStatus']);
    Route::get('plants/statistics/summary', [PlantController::class, 'statistics']);
});