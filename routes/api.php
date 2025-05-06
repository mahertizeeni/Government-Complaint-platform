<?php

use App\Http\Controllers\ContactUsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmartChatController;
use App\Models\ContactUs;

// ChatBot Endpoint
Route::post('/chat', [SmartChatController::class, 'chat']);

//ContactUs Endpoint
Route::post('/contactus',[ContactUsController::class, '__invoke']);

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
