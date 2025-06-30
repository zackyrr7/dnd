<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\GameController;
use Illuminate\Support\Js;

class RoomController extends Controller
{

    public function welcome(Request $request)
    {
        $token = $request->cookie('user_token');



        if ($token == null) {
            $nama = '';
        } else {
            $ambilNama = DB::select("SELECT nickname from users where token = '$token'");
            $nama = $ambilNama[0]->nickname;
        }





        $data = [
            'nama' => $nama
        ];

        return view('welcome')->with($data);
    }


    public function createNickName()
    {
        return view('create_nickname.index');
    }


    public function saveNickName(Request $request)
    {
        try {




            $nickname = $request->nickname;
            $token = bin2hex(random_bytes(32));

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



            $token = $request->cookie('user_token');
            $roomSebelumnya = DB::table('rooms')->where('user_token', $token)->delete();

            $unReady = DB::table('users')->where('token', $token)->update([
                'ready' => 0,
                'role' => null
            ]);

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
            $data = [
                'room' => $kodeRoom
            ];
            return view('room.index')->with($data);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,

                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function listRoom(Request $request)
    {
        $kode = $request->kode;


        $listUser = DB::select("SELECT * FROM (
                                SELECT 
                                    a.nickname,
                                    a.ready,
                                    0 AS urutan
                                FROM users a
                                INNER JOIN rooms b ON a.token = b.user_token
                                WHERE b.room = '$kode'

                                UNION ALL

                                SELECT 
                                    u.nickname,
                                    u.ready,
                                    1 AS urutan
                                FROM users u
                                WHERE u.room = '$kode'
                                AND u.token NOT IN (
                                    SELECT user_token FROM rooms WHERE room = '$kode'
                                )
                            ) AS user_list
                            ORDER BY urutan;
                            ");


        return response()->json($listUser);
    }

    public function joinRoom(Request $request)
    {
        $token = $request->cookie('user_token');
        $room = $request->kode;


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
                    'ready' => 0,
                    'role' => null
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
        $token = $request->cookie('user_token');

        try {
            DB::table('users')->where('token', $token)->update([
                'room' => null,
                'ready' => 0
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
        $token = $request->cookie('user_token');


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
                'ready' => $status,

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
        $token = $request->cookie('user_token');
        $controllGame = new GameController();
        $kode = $request->kode;

        try {

            $cekRoomMaster = DB::table('rooms')
                ->where('user_token', $token)
                ->where('room', $kode)
                ->select('room')
                ->first();

            if (!$cekRoomMaster || $cekRoomMaster->room === null) {
                return response()->json([
                    'status' => true,
                    'is_master' => false,
                    'ready' => null,
                    'message' => 'Menunggu room master memilih cerita'
                ], 200);
            }
            $ready = DB::select("SELECT ready from users where token = '$token'");
            if ($ready[0]->ready == 1) {
                $status = 1;
                $keterangan = 'Tidak Ready';
            } else {
                $status = 1;
                $keterangan = 'Ready!!';
            }


            DB::table('users')->where('token', $token)->update([
                'ready' => $status
            ]);



            $cekReady = DB::select("SELECT count(ready) as total FROM users where room = 
            (SELECT room from users where token = '$token' LIMIT 1) and ready = 0");


            DB::table('rooms')->where('user_token', $token)
                ->where('room', $kode)->update(
                    [

                        'id_cerita' => 0
                    ]

                );

            if ($cekReady[0]->total > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Masih ada yang tidak ready',

                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'is_master' => true,
                    'ready' => null,
                    'message' => 'Memilih Cerita'
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function cekStatusCerita(Request $request)
    {
        $token = $request->cookie('user_token');
        $room = $request->room;

        $status = DB::table('rooms')
            ->where('room', $room)      // where kolom 'room' = $room
            ->value('id_cerita');       // ambil nilai kolom 'id_cerita'


        return response()->json([
            'status' => $status
        ]);
    }
    public function batalPilihCerita(Request $request)
    {
        $token = $request->cookie('user_token');
        $room = $request->room;

        DB::table('rooms')->where('user_token', $token)
            ->where('room', $room)->update(
                [

                    'id_cerita' => null
                ]

            );

        return response()->json([
            'status' => 'ready'
        ]);
    }
}
