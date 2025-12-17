<?php

namespace Pqd\AiContentBuilder\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'max:10000'],
            'system_prompt' => ['nullable', 'string', 'max:5000'],
            'word_limit' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $apiKey = config('ai-content-builder.api_key');
        $model = config('ai-content-builder.model');
        $endpoint = config('ai-content-builder.endpoint');

        if (empty($apiKey)) {
            return response()->json([
                'error' => 'OpenAI API key not configured. Please set OPENAI_API_KEY in your .env file.',
            ], 500);
        }

        $messages = [];

        // Build system prompt with word limit instruction
        $systemContent = $request->input('system_prompt', '');
        $wordLimit = $request->input('word_limit', 250);

        if ($wordLimit > 0) {
            $systemContent .= "\n\nIMPORTANT: Keep your response under {$wordLimit} words.";
        }

        $systemContent .= "\n\nFormat your response in Markdown. Use headings, lists, bold, and other formatting as appropriate for the content.";

        if (! empty(trim($systemContent))) {
            $messages[] = [
                'role' => 'system',
                'content' => trim($systemContent),
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $request->input('prompt'),
        ];

        try {
            $requestBody = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
            ];

            // Calculate approximate max_tokens if word limit is set
            if ($wordLimit > 0) {
                $requestBody['max_tokens'] = (int) round($wordLimit * 1.5);
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($endpoint, $requestBody);

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $errorMessage = 'Failed to generate content.';

                if ($response->status() === 401) {
                    $errorMessage = 'Invalid API key. Please check your OPENAI_API_KEY.';
                } elseif ($response->status() === 429) {
                    $errorMessage = 'Rate limit exceeded. Please try again later.';
                } elseif ($response->status() === 500) {
                    $errorMessage = 'OpenAI service error. Please try again later.';
                }

                return response()->json([
                    'error' => $errorMessage,
                ], $response->status());
            }

            $content = $response->json('choices.0.message.content');

            return response()->json([
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'An error occurred while generating content. Please try again.',
            ], 500);
        }
    }
}
