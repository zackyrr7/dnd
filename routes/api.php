<?php

use App\Http\Controllers\GameController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('saveNickName', [RoomController::class, 'saveNickName'])->name('saveNickname');
Route::post('createRoom', [RoomController::class, 'createRoom'])->name('createRoom');
Route::post('joinRoom', [RoomController::class, 'joinRoom'])->name('joinRoom');
Route::post('leftRoom', [RoomController::class, 'leftRoom'])->name('leftRoom');
Route::post('ready', [RoomController::class, 'ready'])->name('ready');
Route::post('readyAll', [RoomController::class, 'readyAll'])->name('readyAll');


//cerita
Route::post('pilihCerita', [GameController::class, 'pilihCerita'])->name('pilihCerita');
Route::post('simpanPilihCerita', [GameController::class, 'simpanPilihCerita'])->name('simpanPilihCerita');
