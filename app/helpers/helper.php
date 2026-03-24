<?php

use App\Models\AiSetting;
use Illuminate\Support\Facades\Http;

if (! function_exists('gemini_api_response')) {
    function gemini_api_response(string $prompt, ?AiSetting $setting = null): array
    {
        $setting ??= AiSetting::where('title', 'Gemini')->first();

        if (! $setting || ! $setting->api_key || ! $setting->model_name) {
            return [
                'success' => false,
                'provider' => 'Gemini',
                'message' => 'Gemini API configuration is incomplete.',
            ];
        }

        $response = Http::timeout(30)
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/'.$setting->model_name.':generateContent?key='.$setting->api_key,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

        $data = $response->json();
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return [
            'success' => $response->successful() && filled($text),
            'provider' => 'Gemini',
            'message' => $text ?: ($data['error']['message'] ?? 'Gemini response failed.'),
            'raw' => $data,
        ];
    }
}

if (! function_exists('claude_api_response')) {
    function claude_api_response(string $prompt, ?AiSetting $setting = null): array
    {
        $setting ??= AiSetting::where('title', 'Claude')->first();

        if (! $setting || ! $setting->api_key || ! $setting->model_name) {
            return [
                'success' => false,
                'provider' => 'Claude',
                'message' => 'Claude API configuration is incomplete.',
            ];
        }

        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $setting->api_key,
                'anthropic-version' => '2023-06-01',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $setting->model_name,
                'max_tokens' => 512,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        $data = $response->json();
        $text = $data['content'][0]['text'] ?? null;

        return [
            'success' => $response->successful() && filled($text),
            'provider' => 'Claude',
            'message' => $text ?: ($data['error']['message'] ?? 'Claude response failed.'),
            'raw' => $data,
        ];
    }
}

if (! function_exists('openai_api_response')) {
    function openai_api_response(string $prompt, ?AiSetting $setting = null): array
    {
        $setting ??= AiSetting::where('title', 'OpenAI')->first();

        if (! $setting || ! $setting->api_key || ! $setting->model_name) {
            return [
                'success' => false,
                'provider' => 'OpenAI',
                'message' => 'OpenAI API configuration is incomplete.',
            ];
        }

        $response = Http::timeout(30)
            ->withToken($setting->api_key)
            ->post('https://api.openai.com/v1/responses', [
                'model' => $setting->model_name,
                'input' => $prompt,
            ]);

        $data = $response->json();
        $text = $data['output'][0]['content'][0]['text'] ?? null;

        return [
            'success' => $response->successful() && filled($text),
            'provider' => 'OpenAI',
            'message' => $text ?: ($data['error']['message'] ?? 'OpenAI response failed.'),
            'raw' => $data,
        ];
    }
}

if (! function_exists('active_ai_response')) {
    function active_ai_response(string $prompt): array
    {
        $setting = AiSetting::where('is_active', true)->first();

        if (! $setting) {
            return [
                'success' => false,
                'provider' => null,
                'message' => 'No active AI provider found.',
            ];
        }

        return match (strtolower($setting->title)) {
            'gemini' => gemini_api_response($prompt, $setting),
            'claude' => claude_api_response($prompt, $setting),
            'openai' => openai_api_response($prompt, $setting),
            default => [
                'success' => false,
                'provider' => $setting->title,
                'message' => 'Unsupported AI provider selected.',
            ],
        };
    }
}
