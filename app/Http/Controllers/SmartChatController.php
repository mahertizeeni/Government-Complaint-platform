<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Complaint;

use Illuminate\Http\Request;
use App\Models\GovernmentEntity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SmartChatController extends Controller
{
    
 
    
 public function chat(Request $request)
{
    $userMessage = $request->input('message');

    if (!$userMessage) {
        return response()->json(['error' => 'الرسالة مطلوبة.'], 400);
    }

    $systemMessage = [
        'role' => 'system',
        'content' => "أنت مساعد ذكي لمنصة تقديم الشكاوى الإلكترونية.
دورك هو مساعدة المستخدمين في تعبئة تفاصيل شكواهم بطريقة مبسطة ومنظمة.
استخدم اللغة العربية الفصحى المبسطة، وتواصل بلطف واحترافية.
ابدأ دائماً برسالة ترحيبية قصيرة عندما تكون أول رسالة.
ثم اسأل المستخدم عن موضوع شكواه بشكل عام.

بعد أن يحدد المستخدم موضوع الشكوى:
- حاول أن تستنتج بنفسك الجهة المرتبطة بالشكوى بناءً على الموضوع (الخيارات: الكهرباء، المياه، البلدية، المالية، العقارية).
- إذا لم يكن واضحاً، اطلب من المستخدم تحديد الجهة بشكل مباشر.

بعد تحديد الجهة، تابع الأسئلة بالتسلسل التالي، سؤالاً واحداً في كل مرة، وانتظر إجابة المستخدم قبل المتابعة:
1. ما هو موضوع الشكوى؟ (إذا لم يكن واضحًا من البداية)
2. ماذا حدث بالتفصيل؟
3. متى وأين وقع الحادث أو المشكلة؟
4. من هم الأطراف أو الجهات المشاركة أو المسؤولة؟
5. ما هو العنوان أو الموقع المرتبط بالشكوى؟
6. هل لديك مستندات أو صور داعمة للشكوى؟

ملاحظات مهمة:
- لا ترسل أكثر من سؤال في نفس الوقت.
- لا تُنهي المحادثة إلا بعد جمع جميع المعلومات المذكورة.
- كن صبوراً وشجع المستخدم إذا كانت إجاباته قصيرة أو غير واضحة.
- إذا كانت إجابة المستخدم غامضة، اطلب منه التوضيح بطريقة مهذبة.

بعد جمع جميع التفاصيل:
- قم بتلخيص الشكوى بشكل مرتب وواضح.
- ثم اسأل المستخدم: (هل أنت متأكد أنك تريد تقديم الشكوى بهذه التفاصيل؟)

لا تقدم الشكوى قبل التأكد من موافقة المستخدم النهائية.
تصرف بلطف واحترام كامل طوال المحادثة."
    ];

    $chatHistory = Session::get('chat_history', []);
    $chatHistory[] = ['role' => 'user', 'content' => $userMessage];

    $messages = array_merge([$systemMessage], $chatHistory);

    $payload = [
        'model' => 'allam-2-7b',
        'messages' => $messages,
          'temperature' => 0.3
    ];

    $apikey = ''; 

    try {
        $response = Http::timeout(30)
            ->connectTimeout(10)
            ->retry(3, 2000)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apikey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

        if ($response->successful()) {
            $aiReply = $response->json()['choices'][0]['message']['content'];

            $chatHistory[] = ['role' => 'assistant', 'content' => $aiReply];
            Session::put('chat_history', $chatHistory);

            return response()->json(['ai_reply' => $aiReply]);
        } else {
            return response()->json(['error' => 'حدث خطأ أثناء الاتصال بخدمة Groq'], 500);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




     
//     public function chat(Request $request)
//     {
//         $userMessage = $request->input('message');

//         if (!$userMessage) {
//             return response()->json(['error' => 'الرسالة مطلوبة.'], 400);
//         }
//         // قواعد النظام

//         $systemMessage = [
//             'role' => 'system',
// 'content' => "أنت مساعد ذكي لمنصة تقديم الشكاوى الإلكترونية.

// دورك هو مساعدة المستخدم في جمع تفاصيل شكواه خطوة بخطوة بطريقة مبسطة ومنظمة.

// **قواعد سلوك صارمة يجب اتباعها:**

// 1. **ابدأ دائماً** برسالة ترحيبية قصيرة إذا كانت هذه أول رسالة.
// 2. **اسأل سؤالاً واحداً فقط في كل مرة.** لا تسأل أكثر من سؤال معاً.
// 3. **لا تنتقل للسؤال التالي** إلا بعد أن يجيب المستخدم.
// 4. **كن صبوراً ومشجعاً،** خاصة إذا كانت الإجابة قصيرة أو غير واضحة.
// 5. **إذا كانت الإجابة غير مفهومة،** اطلب التوضيح بلطف.
// 6. **لا تُنهِ المحادثة** حتى تجمع كل المعلومات التالية، بالترتيب:

//    - ما هو موضوع الشكوى؟
//    - ماذا حدث بالتفصيل؟
//    - متى وأين وقع الحادث أو المشكلة؟
//    - من هم الأطراف أو الجهات المشاركة أو المسؤولة؟
//    - ما هو العنوان أو الموقع المرتبط بالشكوى؟
//    - هل لديك مستندات أو صور داعمة للشكوى؟

// 7. بعد جمع كل المعلومات، **قم بتلخيص الشكوى بشكل منظم وواضح.**
// 8. اسأل المستخدم في النهاية:  
//    **هل أنت متأكد أنك تريد تقديم الشكوى بهذه التفاصيل؟**

// 9. لا تقم بتقديم الشكوى فعلياً، فقط انتظر الموافقة النهائية من المستخدم.

// **التزم باللغة العربية الفصحى المبسطة، وتحدث بلطف واحترافية.**

// إذا خالفت أي قاعدة من هذه القواعد، فإن ردك غير مقبول."

//         ];

//         $payload = [
//             'model' => 'allam-2-7b',
//             'messages' => [
//                 $systemMessage,
//                 ['role' => 'user', 'content' => $userMessage],
//             ]
//         ];
//         $apikey = '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';
//         try {
//             $response = Http::timeout(30)
//                 ->connectTimeout(10)
//                 ->retry(3, 2000)
//                 ->withHeaders([
//                     'Authorization' => 'Bearer ' .$apikey,
//                     'Content-Type' => 'application/json',
//                 ])
//                 ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

//             if ($response->successful()) {
//                 $aiReply = $response->json()['choices'][0]['message']['content'];
//                 // تخزين المدينة في الجلسة
//                 if ($userMessage == 'المدينة') {
//                    $city =City::where('name',$userMessage)->first();
//                    if($city){
//                     Session::put('city_id',$city->id);
//                     $aiReply = "تم تحديد الجهة".$city->name ; 
//                    }
//                    else {
//                     $allCities=City::pluck('name')->toarray();
//                     $aiReply = "لم يتم ايجاد المدينة الرجاء اختيار احدى ".implode(', ',$allCities);
//                    }}
//                 //    تخزين الجهة في الجلسة
                   
//                 if ($userMessage == 'الجهة') {
//                    $governmentEntity =GovernmentEntity::where('name',$userMessage)->first();
//                    if($governmentEntity){
//                     Session::put('government_entity_id',$governmentEntity->id);
//                     $aiReply = "تم تحديد الجهة".$governmentEntity->name ; 
//                    }
//                    else {
//                     $allEntities=GovernmentEntity::pluck('name')->toarray();
//                     $aiReply = "لم يتم ايجاد الجهة الرجاء اختيار احدى ".implode(', ',$allEntities);
//                    }
                   
//                 }

//                 return response()->json([
//                     'ai_reply' => $aiReply
//                 ]);
//             } else {
//                 return response()->json(['error' => 'حدث خطأ أثناء الاتصال بخدمة Groq'], 500);
//             }
//         } catch (\Exception $e) {
//             return response()->json(['error' => $e->getMessage()], 500);
//         }
        
//     }
//     public function submitComplaint(Request $request)
//     {
//         // استرجاع البيانات من الجلسة
//         $city_id = Session::get('city_id');
//         $government_entity_id = Session::get('government_entity_id');
//         $description = $request->input('description');
//         $attachments = ''; 

//         // تخزين الشكوى في قاعدة البيانات
//         $complaint = new Complaint();
//         $complaint->user_id = auth::id();  
//         $complaint->city_id = $city_id;
//         $complaint->government_entity_id = $government_entity_id;
//         $complaint->description = $description;
//         $complaint->attachments = $attachments;
//         $complaint->is_emergency = false;  
//         $complaint->status = 'pending';
//         $complaint->save();

//         // رد على المستخدم
//         return response()->json(['message' => 'تم تقديم الشكوى بنجاح']);
//     }


// $apikey ='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';
// public function chat(Request $request)
// {
//     $userMessage = trim($request->input('message'));

//     if (!$userMessage) {
//         return response()->json(['error' => 'الرسالة مطلوبة.'], 400);
//     }

//     // جلب التاريخ والحالة
//     $chatHistory = Session::get('chat_history', []);
//     $metadata = Session::get('chat_metadata', [
//         'welcomed' => false,
//         'subject' => null,
//         'entity' => null,
//         'city' => null,
//     ]);

//     // محاولة استنتاج معلومات من رسالة المستخدم
//     if (str_contains($userMessage, 'كهرب')) {
//         $metadata['subject'] = 'انقطاع الكهرباء';
//         $metadata['entity'] = 'الكهرباء';
//     } elseif (str_contains($userMessage, 'ماء')) {
//         $metadata['subject'] = 'مشكلة في المياه';
//         $metadata['entity'] = 'المياه';
//     } elseif (str_contains($userMessage, 'حفرة') || str_contains($userMessage, 'قمامة')) {
//         $metadata['subject'] = 'مشكلة في الشارع';
//         $metadata['entity'] = 'البلدية';
//     } elseif (str_contains($userMessage, 'مالية') || str_contains($userMessage, 'ضريبة')) {
//         $metadata['entity'] = 'المالية';
//     } elseif (str_contains($userMessage, 'عقاري') || str_contains($userMessage, 'طابو')) {
//         $metadata['entity'] = 'العقارية';
//     }

//     if (str_contains($userMessage, 'حلب')) {
//         $metadata['city'] = 'حلب';
//     } elseif (str_contains($userMessage, 'دمشق')) {
//         $metadata['city'] = 'دمشق';
//     }

//     if (!$metadata['welcomed']) {
//         $metadata['welcomed'] = true;
//     }

//     // بناء تذكير للمعلومات المعروفة ليعطي النموذج سياقًا واضحًا في كل مرة
//     $knownFacts = [];
//     if ($metadata['subject']) {
//         $knownFacts[] = "موضوع الشكوى: {$metadata['subject']}";
//     }
//     if ($metadata['entity']) {
//         $knownFacts[] = "الجهة الحكومية: {$metadata['entity']}";
//     }
//     if ($metadata['city']) {
//         $knownFacts[] = "المدينة: {$metadata['city']}";
//     }

//     $contextReminder = count($knownFacts)
//         ? "المعلومات التي جمعناها حتى الآن:\n- " . implode("\n- ", $knownFacts)
//         : "لا توجد معلومات مؤكدة بعد.";

//     // بناء محتوى system prompt مع التذكير بالمعلومات المجمعة
//     $systemContent = "أنت مساعد ذكي لمنصة الشكاوى الحكومية الإلكترونية.\n"
//         . "ابدأ بالترحيب مرة واحدة فقط إذا لم يتم الترحيب سابقًا.\n"
//         . "لا تكرر الترحيب أو الأسئلة التي تمت الإجابة عنها.\n\n"
//         . "$contextReminder\n\n"
//         . "تابع طرح الأسئلة خطوة بخطوة لجمع التفاصيل اللازمة.\n"
//         . "اسأل سؤالًا واحدًا فقط في كل مرة وانتظر الرد.";

//     // تحضير الرسائل لإرسالها للنموذج (system + history + رسالة المستخدم)
//     $messages = [['role' => 'system', 'content' => $systemContent]];
//     foreach ($chatHistory as $msg) {
//         $messages[] = $msg;
//     }
//     $messages[] = ['role' => 'user', 'content' => $userMessage];

//     // تجهيز البايلود
//     $payload = [
//         'model' => 'llama3-70b-8192',
//         'messages' => $messages,
//     ];

//     try {
//         $response = Http::timeout(30)
//             ->connectTimeout(10)
//             ->retry(3, 2000)
//             ->withHeaders([
//                 'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
//                 'Content-Type' => 'application/json',
//             ])
//             ->post('https://api.groq.com/openai/v1/chat/completions', $payload);

//         if ($response->successful()) {
//             $aiReply = $response->json()['choices'][0]['message']['content'];

//             // تحديث سجل المحادثة والبيانات المجمعة في الجلسة
//             $chatHistory[] = ['role' => 'user', 'content' => $userMessage];
//             $chatHistory[] = ['role' => 'assistant', 'content' => $aiReply];
//             Session::put('chat_history', $chatHistory);
//             Session::put('chat_metadata', $metadata);

//             return response()->json(['ai_reply' => $aiReply]);
//         } else {
//             return response()->json(['error' => 'فشل الاتصال بـ Groq API'], 500);
//         }
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'استثناء: ' . $e->getMessage()], 500);
//     }
// }


//     // دالة لإعادة تعيين المحادثة (اختياري)
    public function resetChat()
    {
        Session::forget('chat_step');
        Session::forget('complaint_data');

        return response()->json([
            'ai_reply' => 'تمت إعادة تعيين المحادثة. يمكنك البدء من جديد.'
        ]);
    }
}