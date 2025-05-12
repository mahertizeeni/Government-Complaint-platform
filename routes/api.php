<?php

<<<<<<< HEAD
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
}); */
Route::get('/user', function (Request $request) {
    return 'hello from api ';
});
/* Route::controller(AuthController::class)->group(function()
{
    Route::post('register','register');
}); */
//Route::post('/register', [AuthController::class, 'register']);
=======
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\employee\auth\AuthController;
use App\Http\Controllers\EmployeeComplaintsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmartChatController;
use App\Models\ContactUs;

// ChatBot Endpoint
Route::post('/chat', [SmartChatController::class, 'chat']);

//ContactUs Endpoint
Route::post('/contactus',[ContactUsController::class, '__invoke']);
Route::controller(AuthController::class)->group(function(){
    
});
// Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('complaints',[EmployeeComplaintsController::class,'getComplaints']);
    });
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
>>>>>>> origin/develop
