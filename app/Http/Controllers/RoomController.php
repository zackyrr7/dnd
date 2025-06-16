<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\GameController;

class RoomController extends Controller
{
    public function saveNickName(Request $request)
    {
        try {
            $nickname = $request->input('nickname'); // ambil nickname dari request
            $token = bin2hex(random_bytes(32)); // buat token random

            DB::table('users')->insert([
                'nickname' => $nickname,
                'token' => $token
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Nickname berhasil disimpan',
                'token' => $token
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }


    public function createRoom(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $roomSebelumnya = DB::table('rooms')->where('user_token', $token)->delete();



            do {
                $kodeRoom = Str::upper(Str::random(6));
                $exists = DB::table('rooms')->where('room', $kodeRoom)->exists();
            } while ($exists);



            DB::table('rooms')->insert([
                'user_token' => $token,
                'room' => $kodeRoom,
            ]);
            DB::table('users')
                ->where('token', $token)
                ->update([
                    'room' => $kodeRoom,
                ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Room Berhasil Dibuat',
                'room' => $kodeRoom

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,

                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function joinRoom(Request $request)
    {
        $token = $request->bearerToken();
        $room = $request->room;


        try {
            // Cek apakah room valid
            $cari = DB::table('rooms')->where('room', $room)->first();

            if (!$cari) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kode room salah'
                ], 404);
            }

            // Update room untuk user yang sesuai token
            DB::table('users')
                ->where('token', $token)
                ->update([
                    'room' => $room,
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil join Room',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function leftRoom(Request $request)
    {
        $token = $request->bearerToken();

        try {
            DB::table('users')->where('token', $token)->update([
                'room' => null
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil keluar Room',

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,

                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }


    public function ready(Request $request)
    {
        $token = $request->bearerToken();


        try {
            $ready = DB::select("SELECT ready from users where token = '$token'");
            if ($ready[0]->ready == 1) {
                $status = 0;
                $keterangan = 'Tidak Ready';
            } else {
                $status = 1;
                $keterangan = 'Ready!!';
            }


            DB::table('users')->where('token', $token)->update([
                'ready' => $status
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => $keterangan,

            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,

                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function readyAll(Request $request)
    {
        $token = $request->bearerToken();
        $controllGame = new GameController();

        try {

            $cekRoomMaster = DB::table('rooms')
                ->where('user_token', $token)
                ->select('room')
                ->first();

            if (!$cekRoomMaster || $cekRoomMaster->room === null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda bukan room master',
                ], 200);
            }


            $cekReady = DB::select("SELECT count(ready) as total FROM users where room = 
            (SELECT room from users where token = '$token' LIMIT 1) and ready = 0");


            if ($cekReady[0]->total > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Masih ada yang tidak ready',

                ], 200);
            } else {
                return $controllGame->pilihCerita();
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }
}
