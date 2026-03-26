<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\FbSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FbWebhookController extends Controller
{
    private function buildFaqContext(): string
    {
        $faqs = Faq::query()
            ->select('question', 'answer', 'keyword')
            ->latest()
            ->take(20)
            ->get();

        if ($faqs->isEmpty()) {
            return 'FAQ data not available.';
        }

        return $faqs->map(function (Faq $faq, int $index) {
            return ($index + 1).'. Question: '.$faq->question
                .' | Answer: '.$faq->answer
                .' | Keyword: '.($faq->keyword ?: 'N/A');
        })->implode("\n");
    }

    private function buildProductContext(): string
    {
        $products = Product::query()
            ->where('status', 'active')
            ->select('name', 'selling_price', 'short_description', 'long_description', 'keywords')
            ->oldest()
            ->take(20)
            ->get();

        if ($products->isEmpty()) {
            return 'Product data not available.';
        }

        return $products->map(function (Product $product, int $index) {
            return ($index + 1).'. Product: '.$product->name
                .' | Price: '.$product->selling_price
                .' | Short Description: '.($product->short_description ?: 'N/A')
                .' | Details: '.($product->long_description ?: 'N/A')
                .' | Keywords: '.($product->keywords ?: 'N/A');
        })->implode("\n");
    }

    private function buildAiPrompt(User $user, string $message): string
    {
        $faqContext = $this->buildFaqContext();
        $productContext = $this->buildProductContext();

        return <<<PROMPT
তুমি MJS Organic-এর বাংলা কাস্টমার সাপোর্ট ও সেলস অ্যাসিস্ট্যান্ট।

কঠোর নিয়ম:
- সব উত্তর শুধু বাংলায় লিখবে।
- সালাম দিবে, নমস্কার বলবে না।
- উত্তর এক বাক্যে দিন, প্রয়োজন হলে সর্বোচ্চ দুইটি।
- অপ্রয়োজনীয় ব্যাখ্যা, বড় paragraph, bullet list বা লম্বা marketing লেখা দেবে না।
- কাস্টমারকে অর্ডার করতে আগ্রহী করার চেষ্টা করবে।
- প্রোডাক্ট তথ্য থাকলে ইতিবাচক, বাস্তবসম্মত ও বিক্রয়-সহায়কভাবে বলবে।
- FAQ থেকে উত্তর পাওয়া গেলে FAQ অনুযায়ী উত্তর দেবে।
- ভুল তথ্য বানাবে না।
- তথ্য না পেলে বা নিশ্চিত না হলে অবশ্যই বলবে:
"দুঃখিত, এই বিষয়ে এই মুহূর্তে সঠিক তথ্য দিতে পারছি না। অনুগ্রহ করে আমাদের WhatsApp নম্বর 01309003117 এ যোগাযোগ করুন।"
- প্রয়োজনে খুব ছোটভাবে বলবে: "নাম, মোবাইল নম্বর ও ঠিকানা দিলে অর্ডার প্রসেস করে দিতে পারি।"
- উত্তর সংক্ষিপ্ত, মানবিক, সহায়ক ও বিক্রয়মুখী হবে।

Customer Name: {$user->name}
Customer Message: {$message}

FAQ Data:
{$faqContext}

Product Data:
{$productContext}
PROMPT;
    }

    public function verify(Request $request)
    {
        $verifyToken = env('FB_VERIFY_TOKEN', 'mjs-organic-webhook');

        if (
            $request->input('hub_mode') === 'subscribe' &&
            $request->input('hub_verify_token') === $verifyToken
        ) {
            return response($request->input('hub_challenge'), 200);
        }

        return response('Invalid verify token.', 403);
    }

    public function receive(Request $request)
    {
        $payload = $request->all();

        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['messaging'] ?? []) as $event) {
                $senderPsid = $event['sender']['id'] ?? null;
                $messageText = trim((string) ($event['message']['text'] ?? ''));

                if (! $senderPsid || $messageText === '' || isset($event['message']['is_echo'])) {
                    continue;
                }

                try {
                    $user = User::firstOrCreate(
                        ['PSID_OF_USER' => $senderPsid],
                        [
                            'name' => 'Facebook User',
                            'phone' => 'fb-'.$senderPsid,
                            'password' => bcrypt('fb-user-'.uniqid()),
                        ]
                    );

                    $aiPrompt = $this->buildAiPrompt($user, $messageText);
                    $aiResponse = active_ai_response($aiPrompt);
                    $replyText = $aiResponse['success']
                        ? $aiResponse['message']
                        : 'দুঃখিত, এই বিষয়ে এই মুহূর্তে সঠিক তথ্য দিতে পারছি না। অনুগ্রহ করে আমাদের WhatsApp নম্বর 01309003117 এ যোগাযোগ করুন।';

                    fb_send_page_message($senderPsid, $replyText);
                } catch (\Throwable $exception) {
                    Log::error('Facebook webhook message handling failed.', [
                        'message' => $exception->getMessage(),
                        'payload' => $event,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'EVENT_RECEIVED']);
    }
}

