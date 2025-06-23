<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;


Route::get('/test-redis', function() {
    try {
        Redis::set('test_key', 'Hello Redis!');
        return Redis::get('test_key');
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});



Route::get('/clear-chat', function () {
    session()->forget('chat_history');
    return 'تت تم مسح المحادثة القديمة.';
});


require __DIR__.'/auth.php';
