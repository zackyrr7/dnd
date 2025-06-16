<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;

class GeminiController extends Controller
{
    public function ask(GeminiService $gemini)
    {
        $question = 'Apa itu Laravel?';
        $response = $gemini->ask($question);

        return response()->json(['answer' => $response]);
    }
}
