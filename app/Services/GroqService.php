<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    protected $apiKey;
    protected $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = env('GROQ_API_KEY');
    }

    public function generateResponse(array $conversationHistory): string
    {
        $messages = $this->preparePrompt($conversationHistory);

        // سجل الرسائل المُرسلة إلى النموذج (للمراجعة)
        Log::info('Prepared messages for Groq:', $messages);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'HTTP-Referer' => 'http://localhost'
        ])->post($this->endpoint, [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $messages,
            'temperature' => 0.3,  // خفض درجة العشوائية لتقليل الأخطاء اللغوية
        ]);

        Log::info('Groq API response: ' . $response->body());

        if ($response->failed()) {
            Log::error('Groq API call failed: ' . $response->body());
            return 'فشل في الاتصال بالخدمة الخارجية';
        }

        return $response->json()['choices'][0]['message']['content'] ?? 'عذرًا، حدث خطأ في المعالجة';
    }

  protected function preparePrompt(array $history): array
{
    $systemMessage = [
        'role' => 'system',
        'content' => <<<EOT
أنت مساعد ذكي يتحدث فقط باللغة العربية الفصحى.  
مهمتك هي مساعدة المواطن في تقديم شكوى عبر جمع المعلومات التالية:
1. تفاصيل الحادثة  
2. الجهة الحكومية  
3. المدينة

❌ لا تطلب بريدًا إلكترونيًا أو رقم هاتف  
✅ لا تكرر الأسئلة التي تمّت الإجابة عليها  
✅ تابع من حيث توقفت فقط
EOT
    ];

    $messages = [$systemMessage];

    $collected = [
        'تفاصيل الحادثة' => false,
        'الجهة الحكومية' => false,
        'المدينة' => false,
    ];

    foreach ($history as $msg) {
        $role = $msg['is_bot'] ? 'assistant' : 'user';
        $content = $msg['content'];

        if (mb_strlen($content) > 40) {
            $collected['تفاصيل الحادثة'] = true;
        }

        if (preg_match('/(وزارة|بلدية|التربية|الكهرباء|الصحة)/ui', $content)) {
            $collected['الجهة الحكومية'] = true;
        }

        if (preg_match('/(دمشق|حلب|حمص|اللاذقية|طرطوس|الرياض|جدة|مكة)/ui', $content)) {
            $collected['المدينة'] = true;
        }

        $messages[] = compact('role', 'content');
    }

    // أضف رسالة system ذكية في النهاية لتوجيه البوت
    $summary = "ملخص المعلومات التي تم جمعها:\n";
    foreach ($collected as $key => $value) {
        $summary .= "- $key: " . ($value ? '✓ موجود' : '✘ لم يُذكر بعد') . "\n";
    }

    $messages[] = [
        'role' => 'system',
        'content' => <<<EOT
تابع المحادثة بناءً على التالي:

$summary

اسأل فقط عن المعلومات غير الموجودة.
لا تكرر ما تم سؤاله مسبقًا.
EOT
    ];

    return $messages;
}

}
