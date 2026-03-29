<?php

namespace App\Http\Controllers\Front_site;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Conversion;
use App\Models\Faq;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    private function normalizeMessageForKeywordMatch(string $message): string
    {
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', mb_strtolower($message));

        return trim(preg_replace('/\s+/', ' ', $normalized ?? ''));
    }

    private function findFaqAnswerByKeyword(string $message): ?string
    {
        $normalizedMessage = $this->normalizeMessageForKeywordMatch($message);

        if ($normalizedMessage === '') {
            return null;
        }

        $faqs = Faq::query()
            ->select('question', 'answer', 'keyword')
            ->latest()
            ->get();

        foreach ($faqs as $faq) {
            foreach ($faq->keyword_list as $keyword) {
                $normalizedKeyword = $this->normalizeMessageForKeywordMatch($keyword);

                if ($normalizedKeyword !== '' && str_contains($normalizedMessage, $normalizedKeyword)) {
                    return $faq->answer;
                }
            }
        }

        return null;
    }

    private function normalizeBangladeshPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        if (! $digits) {
            return null;
        }

        if (strlen($digits) >= 11) {
            $digits = substr($digits, -11);
        }

        if (strlen($digits) !== 11 || ! str_starts_with($digits, '01')) {
            return null;
        }

        return $digits;
    }

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
                .' | Keyword: '.($faq->keyword_list ? implode(', ', $faq->keyword_list) : 'N/A');
        })->implode("\n");
    }

    private function buildProductContext(): string
    {
        $products = Product::query()
            ->where('status', 'active')
            ->select('name', 'selling_price', 'short_description', 'long_description', 'keywords')
            ->latest()
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
Customer Phone: {$user->phone}
Customer Message: {$message}

FAQ Data:
{$faqContext}

Product Data:
{$productContext}
PROMPT;
    }

    private function formatChat(Chat $chat): array
    {
        $chat->loadMissing(['user', 'conversions.admin', 'conversions.user']);

        return [
            'chat' => [
                'id' => $chat->id,
                'ticket_number' => $chat->ticket_number,
                'status' => $chat->status,
                'user_name' => $chat->user?->name,
                'user_phone' => $chat->user?->phone,
            ],
            'messages' => $chat->conversions->map(function (Conversion $conversion) {
                return [
                    'id' => $conversion->id,
                    'text' => $conversion->convertion_message,
                    'type' => in_array($conversion->sender_type, ['admin', 'ai'], true) ? 'guest' : 'user',
                    'sender_name' => match ($conversion->sender_type) {
                        'admin' => $conversion->admin?->name ?? 'Admin',
                        'ai' => 'AI Assistant',
                        default => $conversion->user?->name ?? 'User',
                    },
                    'created_at' => $conversion->created_at?->format('Y-m-d H:i'),
                ];
            })->values(),
        ];
    }

    private function findOrCreateChat(User $user): Chat
    {
        $chat = Chat::where('user_id', $user->id)->latest('id')->first();

        if ($chat) {
            return $chat;
        }

        return Chat::create([
            'user_id' => $user->id,
            'ticket_number' => 'CHAT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'open',
            'last_message_at' => now(),
        ]);
    }

    public function history(Request $request)
    {
        $userId = $request->session()->get('user_id');

        if (! $userId) {
            return response()->json([
                'success' => true,
                'registered' => false,
                'messages' => [],
            ]);
        }

        $user = User::find($userId);

        if (! $user) {
            return response()->json([
                'success' => true,
                'registered' => false,
                'messages' => [],
            ]);
        }

        $chat = Chat::with(['user', 'conversions.user', 'conversions.admin'])
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $chat) {
            return response()->json([
                'success' => true,
                'registered' => true,
                'user' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                ],
                'messages' => [],
            ]);
        }

        return response()->json(array_merge([
            'success' => true,
            'registered' => true,
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
            ],
        ], $this->formatChat($chat)));
    }

    public function storeMessage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:5000',
        ]);

        try {
            $payload = DB::transaction(function () use ($request, $validated) {
                $normalizedPhone = $this->normalizeBangladeshPhone($validated['phone']);

                if (! $normalizedPhone) {
                    throw new \InvalidArgumentException('Please enter a valid 11 digit phone number.');
                }

                $user = User::where('phone', $normalizedPhone)->first();

                if ($user) {
                    $user->update([
                        'name' => $validated['name'],
                        'ip_address' => $request->ip(),
                        'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
                        'last_visit_at' => now(),
                        'last_logged_at' => now(),
                    ]);
                } else {
                    $user = User::create([
                        'name' => $validated['name'],
                        'phone' => $normalizedPhone,
                        'password' => Hash::make('chat-user-'.uniqid()),
                        'ip_address' => $request->ip(),
                        'last_user_agent' => substr((string) $request->userAgent(), 0, 65535),
                        'last_visit_at' => now(),
                        'last_logged_at' => now(),
                    ]);
                }

                $chat = $this->findOrCreateChat($user);

                $chat->conversions()->create([
                    'user_id' => $user->id,
                    'sender_type' => 'user',
                    'convertion_message' => $validated['message'],
                ]);

                $directFaqAnswer = $this->findFaqAnswerByKeyword($validated['message']);
                $replyMessage = $directFaqAnswer;

                if (! $replyMessage) {
                    $aiPrompt = $this->buildAiPrompt($user, $validated['message']);
                    $aiResponse = active_ai_response($aiPrompt);
                    $replyMessage = $aiResponse['success']
                        ? $aiResponse['message']
                        : 'AI assistant is currently unavailable. Please wait for a support reply.';
                }

                $chat->conversions()->create([
                    'user_id' => $user->id,
                    'sender_type' => 'ai',
                    'convertion_message' => $replyMessage,
                ]);

                $chat->update([
                    'status' => 'open',
                    'last_message_at' => now(),
                ]);

                $request->session()->put([
                    'user_id' => $user->id,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                ]);

                return $chat->fresh(['user', 'conversions.user', 'conversions.admin']);
            });

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Message sent successfully.',
            ], $this->formatChat($payload)));
        } catch (\Throwable $exception) {
            Log::error('Chat message store failed.', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'phone' => $validated['phone'],
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception instanceof \InvalidArgumentException ? $exception->getMessage() : 'Chat message save failed.',
            ], $exception instanceof \InvalidArgumentException ? 422 : 500);
        }
    }
}
