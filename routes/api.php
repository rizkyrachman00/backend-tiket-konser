<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ConcertController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {

  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/register', [AuthController::class, 'register']);

  Route::get('/me', [AuthController::class, 'me'])->middleware(['auth:sanctum']);
  Route::get('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
});


Route::middleware(['auth:sanctum'])->group(function () {

  // method POST hanya sementara, ada kemungkinan dirubah. bisa saja GET
  // jangan lupa edit middleware di kernel
  // testing bisa melalui POSTMAN
  Route::patch('/edit-user/{id}', [UserController::class, 'edit'])->middleware(['ableCreateOrder']);

  Route::get('/concert', [ConcertController::class, 'index']);
  Route::post('/concert', [ConcertController::class, 'store'])->middleware(['ableCreateUpdateConcert']);
  Route::patch('/concert/{id}', [ConcertController::class, 'update'])->middleware(['ableCreateUpdateConcert']);

  Route::get('/order', [OrderController::class, 'index'])->middleware(['ableCreateUpdateConcert']);
  Route::get('/order/{id}', [OrderController::class, 'show']);
  Route::get('/order/{id}/set-as-paid', [OrderController::class, 'setAsPaid'])->middleware(['ableCreateOrder']);

  Route::post('/order', [OrderController::class, 'store'])->middleware(['ableCreateOrder']);
});
