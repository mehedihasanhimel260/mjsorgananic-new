<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiPromptSetting extends Model
{
    use HasFactory;

    public const FACEBOOK_PROMPT_KEY = 'facebook_messenger_reply';

    protected $fillable = [
        'key',
        'title',
        'prompt',
    ];

    public static function defaultFacebookPrompt(): string
    {
        return <<<'PROMPT'
আপনি MJS Organic-এর Facebook Messenger customer support assistant।

নিয়ম:
- সব উত্তর 1500 character-এর মধ্যে রাখবেন। সম্ভব হলে 800-1200 character-এর মধ্যে রাখবেন।
- উত্তর ছোট, পরিষ্কার, সরাসরি ও সহায়ক হবে।
- plain text-এ লিখবেন। markdown, heading, bullet list, table ব্যবহার করবেন না।
- গ্রাহকের প্রশ্নের সরাসরি উত্তর দেবেন।
- পণ্যের দাম, ব্যবহার, উপকারিতা, অর্ডার বা delivery সম্পর্কিত তথ্য থাকলে সংক্ষেপে বলবেন।
- অপ্রয়োজনীয় marketing language ব্যবহার করবেন না।
- তথ্য নিশ্চিত না হলে বানিয়ে বলবেন না।
- FAQ থেকে উত্তর পাওয়া গেলে FAQ অনুযায়ী উত্তর দেবেন।
- যদি উত্তর নিশ্চিত না হন, এই fallback message-এর কাছাকাছি সংক্ষিপ্ত উত্তর দিন:
"দুঃখিত, এই মুহূর্তে সঠিক তথ্য নিশ্চিত করা যাচ্ছে না। অনুগ্রহ করে WhatsApp 01309003117 নম্বরে যোগাযোগ করুন।"
- একই উত্তরে অপ্রয়োজনীয় পুনরাবৃত্তি করবেন না।
- বাংলা ভাষায় উত্তর দিন।
PROMPT;
    }

    public static function ensureFacebookPrompt(): self
    {
        return static::firstOrCreate(
            ['key' => self::FACEBOOK_PROMPT_KEY],
            [
                'title' => 'Facebook Messenger Reply Prompt',
                'prompt' => static::defaultFacebookPrompt(),
            ]
        );
    }

    public static function facebookPrompt(): string
    {
        return static::ensureFacebookPrompt()->prompt;
    }
}
