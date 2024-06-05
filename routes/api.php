<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::post('/messages', [RoomController::class, 'storeMessage']);
    Route::get('/messages/{roomId}', [RoomController::class, 'messages']);
    Route::get('/search/messages', [RoomController::class, 'searchMessage']);

    Route::get('/users/online', [RoomController::class, 'onlineUsers']);
    Route::get('/users/typing', [RoomController::class, 'userTyping']);
    Route::get('/users/endtyping', [RoomController::class, 'userEndTyping']);

    Route::get('/users/heartbeat', [RoomController::class, 'heartbeat']);
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
});
