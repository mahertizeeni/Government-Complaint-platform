<?php

// use App\Http\Controllers\Api\AuthController;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\SmartChatController;
use App\Http\Controllers\UserComplaintController;
use App\Http\Controllers\CyberComplaintController;
use App\Http\Controllers\employee\auth\AuthController;
use App\Http\Controllers\EmployeeComplaintsController;
use App\Http\Controllers\EmployeeSuggestionController;
use App\Http\Controllers\EmployeeCyberComplaintsController;

/* Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
}); */
Route::get('/user', function (Request $request) {
    return 'hello from api ';
});

########## ChatBot Endpoint
Route::post('/chat', [SmartChatController::class, 'chat']);

######### ContactUs Endpoint
Route::post('/contactus',[ContactUsController::class, '__invoke']);
Route::controller(AuthController::class)->group(function(){

});
// Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

