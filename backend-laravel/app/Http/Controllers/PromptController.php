<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prompt;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    /**
     * Get prompt libraries list
     */
    public function index()
    {
        $prompts = Prompt::orderBy('created_at', 'desc')->get();
        return response()->json($prompts);
    }

    /**
     * Store a prompt version
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'prompt_text' => 'required|string',
            'category' => 'required|in:tiktok,flow,producer,ltx,gemini,hr,marketing',
        ]);

        $prompt = Prompt::create([
            'creator_id' => Auth::id(),
            'title' => $validated['title'],
            'prompt_text' => $validated['prompt_text'],
            'category' => $validated['category'],
            'version' => 1,
            'is_favorite' => false
        ]);

        return response()->json($prompt, 201);
    }

    /**
     * Polishes a prompt template using OpenAI / Gemini integration (Simulated)
     */
    public function generateAI(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string'
        ]);

        // Integration simulation: polishes the draft with structuring rules
        $polishedPrompt = "[AI ENHANCED PROMPT]\n\n" . 
                          "Role: Expert Social Media Video Director & DecorArte Brand Ambassador.\n\n" . 
                          "Input Context: " . $request->input('prompt') . "\n\n" . 
                          "Tone: Modern, premium, sensory, energetic.\n\n" . 
                          "Detailed Visual Instructions:\n" . 
                          "- Scene 1: Macro panning shot of ingredients being poured onto a scale. Depth of field (F/1.8).\n" . 
                          "- Scene 2: Focus on user hands folding chocolate cream. Smooth slow-motion (120fps).\n" . 
                          "- Scene 3: Final reveal with vibrant color grading, high contrast.\n\n" . 
                          "CTA Overlay: Escribe 'QUIERO APRENDER' en comentarios para obtener un 20% de descuento en DecorArte Academia.";

        return response()->json([
            'polishedPrompt' => $polishedPrompt
        ]);
    }
}
