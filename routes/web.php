<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Redis;

Route::get('/',function (){
dd('Welcome to Government Complaints Platform');
});

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

Route::get('/test-cloudinary', function () {
    $filePath = public_path('test.jpg');

    if (!file_exists($filePath)) {
        return response("⚠️ File not found at: $filePath", 404);
    }

    try {
        $uploaded = Cloudinary::upload($filePath);
        return response("✅ Uploaded! URL: " . $uploaded->getSecurePath());
    } catch (\Exception $e) {
        return response("❌ Error: " . $e->getMessage(), 500);
    }
});

Route::get('/test-redis', function() {
    try {
        Redis::set('test_key', 'Hello Redis!');
        return Redis::get('test_key');
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

// Route::get('/test-redis', function() {
//     try {
//         Redis::set('test_key', 'Hello Redis!');
//         return Redis::get('test_key');
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage();
//     }
// });



// Route::get('/clear-chat', function () {
//     session()->forget('chat_history');
//     return 'تت تم مسح المحادثة القديمة.';
// });


require __DIR__.'/auth.php';
