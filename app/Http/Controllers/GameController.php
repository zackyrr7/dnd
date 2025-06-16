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
        $token = $request->bearerToken();
        $ceritaId = $request->cerita;
    
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
    
            $cerita = DB::table('cerita')->where('id', $ceritaId)->first();
            if (!$cerita) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cerita tidak ditemukan'
                ], 404);
            }
    
            $jumlahPemain = DB::table('users')->where('room', $cekRoomMaster->room)->count();
    
            // Panggil helper Gemini
            $roles = $this->callGeminiGenerateRoles($gemini, $cerita->judul, $jumlahPemain);
    
            DB::table('rooms')->where('room', $cekRoomMaster->room)->update([
                'id_cerita' => $cerita->id,
                'generated_story' => $cerita->premis,
                'generated_roles' => json_encode($roles),
                'status_game' => 'pilih_role',
                'story_log' => json_encode([]),
                'current_turn_token' => null
            ]);
    
            return response()->json([
                'status' => true,
                'roles' => $roles
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $th->getMessage()
            ], 500);
        }
    }
    
    private function callGeminiGenerateRoles(GeminiService $gemini, string $judul, int $jumlahPemain)
    {
        $prompt = "Saya sedang membuat game roleplay berbasis cerita. Judul cerita: \"$judul\".
    Tolong buatkan $jumlahPemain role unik yang cocok untuk cerita ini. Jangan beri penjelasan tambahan. 
    Berikan hanya dalam format JSON array, contoh: [\"Pahlawan\", \"Penjahat\", \"Saksi\"].";
    
        $response = $gemini->ask($prompt);
    
        // Bersihkan jika outputnya mengandung ```json atau sejenisnya
        $clean = trim($response);
        $clean = preg_replace('/^```[a-z]*\s*/', '', $clean); // hapus pembuka markdown
        $clean = preg_replace('/```$/', '', $clean);          // hapus penutup markdown
    
        $roles = json_decode($clean, true);
    
        if (!is_array($roles)) {
            throw new \Exception("Output dari Gemini tidak valid JSON: " . $clean);
        }
    
        return $roles;
    }
    
}
