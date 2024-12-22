<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarehouseController;
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

Route::group(['middleware'=>'api','prefix'=>'auth'],function($router){
    
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/login',[AuthController::class,'login']);
    Route::post('/logout',[AuthController::class,'logout']);
    // Route::get('/profile',[AuthController::class,'profile']);
    // Route::post('/refresh',[AuthController::class,'refresh']);
    // Route::post('/reset', [AuthController::class,'reset_password']);
 });
// Route::controller(WarehouseController::class)->prefix('warehouse')->group(function () {
//     Route::get('/dashboard', 'index') ;
//     Route::post('/signin','register')->middleware('auth:warehouse');
//     Route::post('/login','login')->middleware('auth:warehouse');
//     Route::post('/logout','logout')->middleware('auth:warehouse');
// });

// Route::post('/warehouse/login', [AuthController::class, 'login']);
// Route::post('/warehouse/register', [AuthController::class, 'register']);
// Route::post('/warehouse/logout', [AuthController::class, 'logout'])->middleware('auth:warehouse');