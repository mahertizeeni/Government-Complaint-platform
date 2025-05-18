<?php

// use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\employee\auth\AuthController;
use App\Http\Controllers\EmployeeComplaintsController;
use App\Http\Controllers\SmartChatController;
use App\Models\ContactUs;

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
Route::post('/aa',[ContactUsController::class]);
Route::controller(AuthController::class)->group(function(){
    
});
######## Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        #### middelware fpr making sure of logging in
        Route::post('logout', [AuthController::class, 'logout']);
        #### Get Complaints Gor Employee 
        Route::get('complaints',[EmployeeComplaintsController::class,'getComplaints']);
        #### Update Status
        Route::put('complaints/{id}/status',[EmployeeComplaintsController::class,'updateStatus']);
    });
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
