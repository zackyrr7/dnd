<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\GeminiService;


class GameController extends Controller
{
    public function pilihCerita()
    {
        $cerita = DB::select("SELECT * from cerita");
        return response()->json($cerita);
    }




    public function simpanPilihCerita(Request $request, GeminiService $gemini)
    {
        $token = $request->cookie('user_token');
        $ceritaId = $request->id_cerita;

        try {
            DB::beginTransaction();
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


            $cerita = DB::table('cerita')->where('id', $ceritaId)->first();

            $ceritaId2 = (int) $request->id_cerita;

            if (!$cerita) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cerita tidak ditemukan'
                ], 404);
            }

            DB::table('rooms')->where('room', $cekRoomMaster->room)->update([
                'id_cerita' => $ceritaId2,
                'generated_story' => $cerita->premis,
                // 'generated_roles' => json_encode($roles),
                'status_game' => 'pilih_role',
                'story_log' => json_encode([]),
                'current_turn_token' => null
            ]);
            DB::commit();

            return response()->json([
                'status' => true,

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function callGeminiGenerateRoles(Request $request, GeminiService $gemini)
    {
        try {
            $token = $request->cookie('user_token');
            $cekRoomMaster = DB::table('rooms')
                ->where('user_token', $token)
                ->select('room')
                ->first();
            $room = $cekRoomMaster->room;



            $jumlahPemain = DB::table('users')->where('room', $cekRoomMaster->room)->count();

            $cerita = DB::select("SELECT
                            a.*
                        FROM
                            cerita a
                            INNER JOIN rooms b ON a.id = b.id_cerita 
                        WHERE b.room = '$room'");
            $judul = $cerita[0]->judul;
            $premis = $cerita[0]->premis;
            $genre = $cerita[0]->genre;



            $prompt = "Saya sedang membuat game roleplay berbasis cerita. Judul cerita: \"$judul\" dan dengan
            premis seperti ini : \"$premis\" dengan genre \"$genre\".
            Saya ingin role ini seperti orang biasa saja tidak memiliki kekuatan super tetapi tetap menarik.
            Role ini harus sesuai dengan premis cerita,jumlah pemain dan juga genre cerita.
            Role ini harus dapat membantu pemain untuk berperan sesuai dengan cerita dan dapat membantu mereka dalam menyelesaikan cerita.
            Jangan kasih sifat mereka, hanya role saja.
            Rile ini seperti ahli sejarah.
            Tolong buatkan role untuk $jumlahPemain orang. Jumlah role harus tepat. 
            Jangan lebih maupun kurang, role unik yang cocok untuk cerita ini. Jangan beri penjelasan tambahan. 
            Berikan hanya dalam format JSON array, contoh: [\"Pahlawan\", \"Penjahat\", \"Saksi\"].";
            // dd($prompt);
            $response = $gemini->ask($prompt);

            // Bersihkan jika outputnya mengandung ```json atau sejenisnya
            $clean = trim($response);
            $clean = preg_replace('/^```[a-z]*\s*/', '', $clean); // hapus pembuka markdown
            $clean = preg_replace('/```$/', '', $clean);          // hapus penutup markdown

            $roles = json_decode($clean, true);

            if (!is_array($roles)) {
                throw new \Exception("Output dari Gemini tidak valid JSON: " . $clean);
            }

            DB::table('rooms')->where('room', $cekRoomMaster->room)->update([

                'generated_roles' => json_encode($roles),

            ]);


            return response()->json([
                'status' => true,
                'role' => $roles

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }


    public function cekRoles(Request $request)
    {
        $room = $request->room;
        DB::beginTransaction();
        try {
            $jumlahPemain = DB::select("SELECT COUNT(nickname) as jumlah
        FROM users 
        where room = '$room' and (role is null or role  = '')");

            if ($jumlahPemain[0]->jumlah > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Pemain masih ada yang belum memilih role'
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Pemain sudah memilih semua'
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function pickRole(Request $request)
    {

        $role = $request->role;

        $token = $request->cookie('user_token');

        try {
            DB::table('users')->where('token', $token)->update([
                'role' => $role,
            ]);
            return response()->json([
                'status' => true,
                'message' => "Berhasil menambahkan role"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function listRoleUser(Request $request)
    {
        $room = $request->room;
        DB::beginTransaction();

        try {
            $listRoleUser = DB::select("SELECT nickname,role from users where room = '$room'");
            return $listRoleUser;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }
    public function listRole(Request $request)
    {
        $room = $request->room;
        DB::beginTransaction();


        try {
            $listRole = DB::table('rooms')->where('room', $room)->select('generated_roles')->get();

            return $listRole;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }


    public function cekCerita(Request $request)
    {
        $room = $request->room;

        DB::beginTransaction();
        try {
            $status =  DB::table('rooms')->select('story_step')->where('room', $room)->first();

            return response()->json([
                'status' => $status->story_step
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function cerita(Request $request)
    {
        $room = $request->room;
        DB::beginTransaction();

        try {
            $premis = DB::select("SELECT b.premis from rooms a 
            inner join 
            cerita b on a.id_cerita = b.id
            where a.room = '$room'");

            return $premis[0]->premis;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function mulai(Request $request, GeminiService $gemini)
    {
        $room = $request->room;

        DB::beginTransaction();


        try {
            // Ambil data user
            $roles = DB::table('users')
                ->select(['nickname', 'role'])
                ->where('room', $room)
                ->get();

            //turn
            $turnOrder = $roles->shuffle()->pluck('nickname')->toArray();


            // Ambil premis
            $premisQuery = DB::select("
            SELECT b.* FROM rooms a 
            INNER JOIN cerita b ON a.id_cerita = b.id 
            WHERE a.room = ?
        ", [$room]);

            if (empty($premisQuery)) {
                throw new \Exception("Premis tidak ditemukan untuk room $room");
            }

            $premis = $premisQuery[0]->premis;
            $judul = $premisQuery[0]->judul;
            $genre = $premisQuery[0]->genre;
           


            // Konversi roles ke string terformat
            $roleText = $roles->map(function ($r) {
                return "{$r->nickname} sebagai {$r->role}";
            })->implode(", ");

            // Prompt untuk Gemini
            $prompt = "Tolong buatkan pembukaan cerita untuk game roleplay seperti DND.
            Judul cerita: \"$judul\"
            genre cerita: \"$genre\"
            Premis cerita: \"$premis\"
            Dengan role pemain seperti ini: \"$roleText\"
            
            Kamu adalah Game Master (GM) untuk game ini.
            Jangan bertindak sebagai karakter. Jangan menyapa pemain. Jangan beri penjelasan sistem.
            Jangan terlalu panjang — cukup 3 sampai 5 kalimat singkat.
            - Gunakan kalimat yang jelas dan deskriptif, tanpa jargon teknis dan mudah di mengerti oleh pemain.
            
            Buat suasana awal cerita dan situasi pertama yang harus dihadapi pemain.
            Akhiri dengan pertanyaan terbuka seperti: 'Apa yang kalian lakukan?'
            
            Balasan kamu HARUS berupa narasi pembuka saja dan langsung teks (bukan JSON, bukan markdown, tidak pakai ```).
            ";
            

            // Kirim ke Gemini
            $response = $gemini->ask($prompt);
            $storyLog[] = "GM: {$response}";
            

            // Simpan hasil ke database
            DB::table('rooms')
                ->where('room', $room)
                ->update([
                    'turn_order' => json_encode($turnOrder),
                    // 'generated_story' => $response,
                    'story_log' => json_encode($storyLog),
                    'story_step' => 1,
                    'status_game' => 'started',
                    'current_turn_token' => $turnOrder[0]
                ]);



            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Game dimulai.',
                'story' => $response
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }

    public function getNickname()
    {

        $token = token();

        $nickname = DB::table('users')->where('token', $token)->select('*')->get();

        return $nickname;
    }


    public function showGameRoom(Request $request)
    {
        $room = $request->room;
        $roomData = DB::table('rooms')->where('room', $room)->first();

        if (!$roomData) {
            abort(404, 'Room tidak ditemukan');
        }

        // Decode story_log (array chat)
        $storyLog = json_decode($roomData->story_log, true) ?? [];

        return response()->json([
            'room' => $room,
            'storyLog' => $storyLog,
            'currentTurn' => $roomData->current_turn_token,
            'pending_action' => $roomData->pending_action,
        ]);
    }


    public function actionButton(Request $request, GeminiService $gemini)
    {
        $room = $request->room;
        $nickname = $request->nickname;
        $action = $request->action;

        try {
            $roomData = DB::table('rooms')->where('room', $room)->first();
            if (!$roomData) {
                return response()->json(['status' => false, 'message' => 'Room tidak ditemukan.'], 404);
            }

            $user = DB::table('users')
                ->where('room', $room)
                ->where('nickname', $nickname)
                ->select('nickname', 'role')
                ->first();

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'Pemain tidak ditemukan.'], 404);
            }

            // Ambil story log sebelumnya
            $storyLog = json_decode($roomData->story_log, true) ?? [];

            // Tambahkan aksi pemain ke story_log
            $storyLog[] = "{$nickname}: {$action}";

            // === 1. Cek apakah perlu lempar dadu ===
            $promptCheck = "Dalam sebuah game roleplay, seorang pemain bernama \"$user->nickname\" 
        dengan peran \"$user->role\" melakukan aksi berikut: \"$action\". 
        Apakah aksi ini memerlukan pengocokan dadu? Jawab hanya dengan 'ya' atau 'tidak'.";

            $aiResponse = strtolower(trim($gemini->ask($promptCheck)));

            // === 2. Kalau tidak perlu dadu, lanjut cerita dan next turn ===
            if ($aiResponse === 'tidak') {
                $promptStory = "Kamu adalah Game Master dalam sebuah game roleplay yang memiliki alur cerita berkembang dan akan berakhir setelah tujuan utama tercapai.

Lanjutkan cerita berdasarkan aksi pemain bernama \"$nickname\" yang memegang peran \"$user->role\". 
Aksi yang dilakukan pemain adalah: \"$action\". 
Panggil pemain hanya menggunakan nickname mereka, dan sesuaikan narasi dengan peran yang mereka mainkan.

Tugasmu:
- Ceritakan dampaknya terhadap alur cerita secara menarik dan dinamis.
- Hubungkan aksi ini dengan perkembangan cerita utama atau konflik yang sedang berlangsung.
- Jangan menjawab sebagai karakter, dan jangan menyimpulkan seluruh cerita sekarang.
- Gunakan gaya bahasa naratif seperti narator atau Game Master.
- Maksimal 4-5 kalimat.
- Gunakan kalimat yang jelas dan deskriptif, tanpa jargon teknis dan mudah di mengerti oleh pemain.
- Jangan menambah player lain lagi.
- Tetap berpegang teguh pada premis cerita dengan premis: \"{$roomData->generated_story}\".
- Jangan mengulang cerita yang sudah ada, fokus pada kelanjutan cerita dan jangan buat ceritanya berputar putar.

Arahkan cerita agar perlahan-lahan mendekati tujuan akhir, namun tetap biarkan ruang bagi pemain lain untuk melanjutkan aksi.
Akhiri narasi dengan situasi terbuka, tantangan baru, atau konsekuensi dari aksi yang memberi pilihan selanjutnya.";


                $nextStory = $gemini->ask($promptStory);

                // Tambahkan cerita dari AI ke log
                $storyLog[] = "GM: {$nextStory}";

                // Ambil urutan giliran
                $turnOrder = json_decode($roomData->turn_order, true);
                if (!is_array($turnOrder)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Format turn_order tidak valid.'
                    ], 500);
                }

                // Tentukan pemain selanjutnya
                $currentIndex = array_search($nickname, $turnOrder);
                $nextIndex = ($currentIndex + 1) % count($turnOrder);
                $nextPlayer = $turnOrder[$nextIndex];

                // Simpan ke database
                DB::table('rooms')
                    ->where('room', $room)
                    ->update([
                        'story_log' => json_encode($storyLog),
                        'story_step' => count($storyLog),
                        'current_turn_token' => $nextPlayer,
                        'pending_action' => null
                    ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Cerita berhasil dilanjutkan.',
                    'roll_required' => false,
                    'next_story' => $nextStory,
                    'next_turn' => $nextPlayer
                ]);
            }

            // === 3. Kalau perlu kocok dadu ===
            if ($aiResponse === 'ya') {
                DB::table('rooms')->where('room', $room)->update([
                    'story_log' => json_encode($storyLog),
                    'story_step' => count($storyLog),
                    'pending_action' => $nickname
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Aksi memerlukan pengocokan dadu.',
                    'roll_required' => true,
                    'currentTurn' => $roomData->current_turn_token,
                ]);
            }

            // === 4. Respons AI tidak valid ===
            return response()->json([
                'status' => false,
                'message' => 'Respons AI tidak dikenali: ' . $aiResponse
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }



    public function rollDice(Request $request, GeminiService $gemini)
    {
        $room = $request->room;
        $nickname = $request->nickname;
        $action = $request->action;
        $roll = rand(1, 20);

        $roomData = DB::table('rooms')->where('room', $room)->first();
        if (!$roomData) {
            return response()->json([
                'status' => false,
                'message' => 'Room tidak ditemukan.'
            ], 404);
        }

        // Ambil story_log sebelumnya
        $storyLog = json_decode($roomData->story_log, true) ?? [];

        // Tambahkan info aksi roll ke log
        $storyLog[] = "{$nickname} mengocok dadu dan mendapatkan angka {$roll}.";

        // Buat prompt lanjutan cerita
        $prompt = "Player dengan nickname \"$nickname\" baru saja melakukan pengocokan dadu dan mendapatkan hasil $roll dari 1 sampai 20,
         sebagai respons terhadap aksi yang dia lakukan sebelumnya. Panggil pemain hanya menggunakan nickname mereka.

        Sebagai Game Master (GM), lanjutkan cerita dengan mempertimbangkan hal-hal berikut:
        - Nilai dadu menentukan hasil aksi: 
            - Nilai rendah (1–9) berarti aksi gagal atau memburuk.
            - Nilai sedang (10–14) berarti aksi sebagian berhasil atau netral.
            - Nilai tinggi (15–20) berarti aksi sukses atau sangat berhasil.
        - Buat narasi lanjutan berdasarkan konteks dan data log sebelumnya.
        - Cerita harus koheren dengan aksi terakhir dan masuk akal sesuai hasil dadu.
        - Jangan membuat keputusan untuk pemain lain, cukup ceritakan hasil dan kondisi baru.
        - Gunakan gaya narasi seperti dungeon master dalam game fantasi.
        - Maksimal 4-5 kalimat.
- Gunakan kalimat yang jelas dan deskriptif, tanpa jargon teknis dan mudah di mengerti oleh pemain.
        - Gunakan kalimat yang jelas dan deskriptif, tanpa jargon teknis dan mudah di mengerti oleh pemain.
        - Jangan menambah player lain lagi.
        - Tetap berpegang teguh pada premis cerita dengan premis: \"{$roomData->generated_story}\".
        - Jangan mengulang cerita yang sudah ada, fokus pada kelanjutan cerita dan jangan buat ceritanya berputar putar.

        Arahkan cerita agar perlahan-lahan mendekati tujuan akhir, namun tetap biarkan ruang bagi pemain lain untuk melanjutkan aksi.
Akhiri narasi dengan situasi terbuka, tantangan baru, atau konsekuensi dari aksi yang memberi pilihan selanjutnya.
        
        Berikut adalah log cerita sebelumnya yang bisa kamu jadikan referensi: " . json_encode($storyLog);
        

        // Minta AI untuk melanjutkan cerita berdasarkan hasil dadu
        $hasilDadu = $gemini->ask($prompt);

        // Tambahkan hasil cerita dari AI ke log
        $storyLog[] = "GM: {$hasilDadu}";

        // Ambil urutan giliran dari kolom `turn_order`
        $turnOrder = json_decode($roomData->turn_order, true);
        if (!is_array($turnOrder)) {
            return response()->json([
                'status' => false,
                'message' => 'Format turn_order tidak valid.'
            ], 500);
        }

        // Tentukan siapa pemain selanjutnya
        $currentIndex = array_search($nickname, $turnOrder);
        $nextIndex = ($currentIndex + 1) % count($turnOrder);
        $nextPlayer = $turnOrder[$nextIndex];
        $currentPlayer = $turnOrder[$currentIndex];

        // Simpan perubahan ke DB
        DB::table('rooms')
            ->where('room', $room)
            ->update([
                'story_log' => json_encode($storyLog),
                'story_step' => count($storyLog),
                'current_turn_token' => $currentPlayer,
                'pending_action' => null,
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Cerita berhasil dilanjutkan.',
            'roll_required' => false,
            'next_story' => $hasilDadu,
            'next_turn' => $currentPlayer
        ]);
    }







    // public function aiTurn(Request $request, GeminiService $gemini)
    // {
    //     $room = $request->room;

    //     DB::beginTransaction();
    //     try {
    //         // Ambil data room
    //         $roomData = DB::table('rooms')->where('room', $room)->first();
    //         if (!$roomData) {
    //             return response()->json(['status' => false, 'message' => 'Room tidak ditemukan'], 404);
    //         }

    //         // Ambil cerita log dan giliran sekarang
    //         $storyLog = json_decode($roomData->story_log, true) ?? [];
    //         $currentTurn = $roomData->current_turn_token;

    //         if ($currentTurn !== 'rm') {
    //             // Jika bukan giliran AI, batalkan
    //             return response()->json(['status' => false, 'message' => 'Bukan giliran AI'], 400);
    //         }

    //         // Buat prompt untuk AI, misal:
    //         $lastStory = end($storyLog)['story'] ?? 'Cerita dimulai';

    //         $prompt = "Lanjutkan cerita roleplay seperti DND berdasarkan ini: \"$lastStory\".
    //                Buat satu paragraf pembukaan aksi AI selanjutnya, jangan terlalu panjang.";

    //         // Kirim ke Gemini AI
    //         $aiResponse = $gemini->ask($prompt);

    //         // Tambahkan balasan AI ke story log
    //         $storyLog[] = [
    //             'nickname' => 'rm',
    //             'story' => $aiResponse,
    //             'timestamp' => now()->toDateTimeString(),
    //         ];

    //         // Tentukan giliran pemain berikutnya (ambil user dari table users berdasarkan urutan)
    //         $players = DB::table('users')->where('room', $room)->orderBy('id')->pluck('nickname')->toArray();

    //         if (empty($players)) {
    //             throw new \Exception("Tidak ada pemain di room ini");
    //         }

    //         // Dapatkan indeks pemain berikutnya
    //         $nextIndex = 0; // default ke pemain pertama
    //         $currentPlayerIndex = array_search('rm', $players);
    //         if ($currentPlayerIndex !== false) {
    //             $nextIndex = ($currentPlayerIndex + 1) % count($players);
    //         }

    //         // Update room dengan story baru dan giliran selanjutnya
    //         DB::table('rooms')->where('room', $room)->update([
    //             'story_log' => json_encode($storyLog),
    //             'current_turn_token' => $players[$nextIndex],
    //             'updated_at' => now(),
    //             // reset status game jika perlu
    //             'status_game' => 'started',
    //             'story_step' => $roomData->story_step + 1,
    //         ]);

    //         DB::commit();

    //         return response()->json(['status' => true, 'message' => 'Giliran AI selesai', 'story' => $aiResponse]);
    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         return response()->json(['status' => false, 'message' => 'Error: ' . $th->getMessage()], 500);
    //     }
    // }
}
