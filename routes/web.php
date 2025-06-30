<?php

use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\GameController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome')->name('welcome');
});

Route::get('/ask-gemini', [\App\Http\Controllers\GeminiController::class, 'ask']);

// routes/web.php
Route::get('/set-token', function (Request $request) {
    $token = $request->query('token');
    return redirect('/')->cookie('user_token', $token, 60 * 24 * 7); // 7 hari
});



//nickname
Route::get('/', [RoomController::class, 'welcome'])->name('welcome');
Route::get('/createNickName', [RoomController::class, 'createNickName'])->name('createNickName');
Route::post('saveNickName', [RoomController::class, 'saveNickName'])->name('saveNickname');


//room
Route::get('/room', function (Request $request) {
    $kode = $request->query('kode'); // ambil kode dari URL

    if (!$kode) {
        abort(404, 'Kode room tidak ditemukan.');
    }

    return view('room.index', ['room' => $kode]);
})->name('room.index');
Route::get('createRoom', [RoomController::class, 'createRoom'])->name('createRoom');
Route::get('/list-room', [RoomController::class, 'listRoom'])->name('list.room');
Route::post('joinRoom', [RoomController::class, 'joinRoom'])->name('joinRoom');
Route::post('ready', [RoomController::class, 'ready'])->name('ready');
Route::post('readyAll', [RoomController::class, 'readyAll'])->name('readyAll');
Route::post('leftRoom', [RoomController::class, 'leftRoom'])->name('leftRoom');



//cerita
Route::get('pilihCerita', [GameController::class, 'pilihCerita'])->name('pilihCerita');
Route::get('cekStatusCerita', [RoomController::class, 'cekStatusCerita'])->name('cekStatusCerita');
Route::get('batalPilihCerita', [RoomController::class, 'batalPilihCerita'])->name('batalPilihCerita');
Route::post('simpanPilihCerita', [GameController::class, 'simpanPilihCerita'])->name('simpanPilihCerita');


//role
Route::get('/role', function () {
    return view('role.index'); // sesuai path: resources/views/role/index.blade.php
})->name('halamanRole');
Route::post('callGeminiGenerateRoles', [GameController::class, 'callGeminiGenerateRoles'])->name('getRole');
Route::get('listRole', [GameController::class, 'listRole'])->name('listRole');
Route::get('listRoleUser', [GameController::class, 'listRoleUser'])->name('listRoleUser');
Route::post('pickRole', [GameController::class, 'pickRole'])->name('pickRole');
Route::post('cekRoles', [GameController::class, 'cekRoles'])->name('cekRoles');
Route::get('cerita', [GameController::class, 'cerita'])->name('cerita');
Route::get('cekCerita', [GameController::class, 'cekCerita'])->name('cekCerita');
Route::post('mulai', [GameController::class, 'mulai'])->name('mulai');


//game
Route::get('/game', function () {
    return view('game.index'); // sesuai path: resources/views/role/index.blade.php
})->name('halamanGame');
Route::get('showGameRoom', [GameController::class, 'showGameRoom'])->name('showGameRoom');
Route::post('aiTurn', [GameController::class, 'aiTurn'])->name('aiTurn');
Route::get('getNickname', [GameController::class, 'getNickname'])->name('getNickname');
Route::post('actionButton', [GameController::class, 'actionButton'])->name('actionButton');
Route::post('rollDice', [GameController::class, 'rollDice'])->name('rollDice');