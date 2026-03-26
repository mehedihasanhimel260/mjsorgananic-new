<?php

use App\Models\AiSetting;
use App\Models\Affiliate;
use App\Models\AffiliateLink;
use App\Models\FbSetting;
use App\Models\Product;
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

if (! function_exists('fb_send_page_message')) {
    function fb_send_page_message(string $psid, string $message): array
    {
        $setting = FbSetting::first();

        if (! $setting || ! $setting->access_token) {
            return [
                'success' => false,
                'message' => 'Facebook access token is not configured.',
            ];
        }

        $response = Http::timeout(30)
            ->withToken($setting->access_token)
            ->post('https://graph.facebook.com/v22.0/me/messages', [
                'recipient' => [
                    'id' => $psid,
                ],
                'message' => [
                    'text' => $message,
                ],
            ]);

        $data = $response->json();

        return [
            'success' => $response->successful(),
            'message' => $data['error']['message'] ?? 'ok',
            'raw' => $data,
        ];
    }
}

if (! function_exists('affiliate_tracking_cookie_name')) {
    function affiliate_tracking_cookie_name(): string
    {
        return 'mjs_affiliate_tracking';
    }
}

if (! function_exists('generate_affiliate_tracking_code')) {
    function generate_affiliate_tracking_code(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
        } while (AffiliateLink::where('tracking_code', $code)->exists());

        return $code;
    }
}

if (! function_exists('save_affiliate_attribution')) {
    function save_affiliate_attribution(\Illuminate\Http\Request $request, AffiliateLink $affiliateLink): void
    {
        $payload = [
            'affiliate_id' => $affiliateLink->affiliate_id,
            'tracking_code' => $affiliateLink->tracking_code,
            'product_id' => $affiliateLink->product_id,
        ];

        $request->session()->put('affiliate_tracking', $payload);
        cookie()->queue(cookie(
            affiliate_tracking_cookie_name(),
            json_encode($payload),
            60 * 24 * 30,
            null,
            null,
            false,
            false
        ));
    }
}

if (! function_exists('get_affiliate_attribution')) {
    function get_affiliate_attribution(?\Illuminate\Http\Request $request = null): ?array
    {
        if (! $request) {
            $request = request();
        }

        $sessionData = $request->session()->get('affiliate_tracking');
        if (is_array($sessionData) && ! empty($sessionData['affiliate_id'])) {
            return $sessionData;
        }

        $cookieValue = $request->cookie(affiliate_tracking_cookie_name());
        if (! $cookieValue) {
            return null;
        }

        $decoded = json_decode($cookieValue, true);
        if (! is_array($decoded) || empty($decoded['affiliate_id'])) {
            return null;
        }

        $request->session()->put('affiliate_tracking', $decoded);

        return $decoded;
    }
}

if (! function_exists('clear_affiliate_attribution')) {
    function clear_affiliate_attribution(\Illuminate\Http\Request $request): void
    {
        $request->session()->forget('affiliate_tracking');
        cookie()->queue(cookie()->forget(affiliate_tracking_cookie_name()));
    }
}

if (! function_exists('get_affiliate_share_url')) {
    function get_affiliate_share_url(AffiliateLink $affiliateLink): string
    {
        return url('/ref/'.$affiliateLink->tracking_code);
    }
}
