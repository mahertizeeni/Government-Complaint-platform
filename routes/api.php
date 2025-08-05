<?php

// use App\Http\Controllers\Api\AuthController;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\SmartChatController;
use App\Http\Controllers\ComplaintChatController;
use App\Http\Controllers\UserComplaintController;
use App\Http\Controllers\CyberComplaintController;
use App\Http\Controllers\employee\auth\AuthController;
use App\Http\Controllers\EmployeeComplaintsController;
use App\Http\Controllers\EmployeeSuggestionController;
use App\Http\Controllers\EmployeeCyberComplaintsController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\UserSuggestionController;
use Laravel\Sanctum\Sanctum;



########## ChatBot Endpoint

Route::post('/chatai', [ComplaintChatController::class, 'handleChat'])->middleware('auth:sanctum');




######### ContactUs Endpoint
Route::post('/contactus',[ContactUsController::class, '__invoke']);
######### Complaint Recource Endpoint
Route::apiResource('User-Complaints',UserComplaintController::class)->only('index', 'store', 'show', 'destroy')->middleware('auth:sanctum');
######### CyberComplaint Endpoint
// Route::post('/cybercomplaint',[CyberComplaintController::class,'store']);
Route::apiResource('User-CyberComplaint',CyberComplaintController::class)->only('index', 'store', 'show', 'destroy')->middleware('auth:sanctum');

Route::apiResource('Suggestions',UserSuggestionController::class)->only('index', 'store', 'show', 'destroy')->middleware('auth:sanctum') ;

######## Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        #### middelware for making sure of logging in
        Route::post('logout', [AuthController::class, 'logout']);
        #### Get Complaints Gor Employee 
        Route::get('complaints',[EmployeeComplaintsController::class,'getComplaints']);
        #### Get Complaint Gor Employee By id 
        Route::get('complaint/{id}',[EmployeeComplaintsController::class,'show']);
        #### Update Complaint Status
        Route::put('complaints/{id}/status',[EmployeeComplaintsController::class,'updateStatus']);

        #### Get Suggestion Gor Employee 
        Route::get('suggestion',[EmployeeSuggestionController::class,'getSuggestions']);
        #### Get suggestion Gor Employee By id 
        Route::get('suggestion/{id}',[EmployeeSuggestionController::class,'show']);

        #### Get Cybercomplaints Gor Employee 
        Route::get('cybercomplaints',[EmployeeCyberComplaintsController::class,'getComplaints']);
         #### Get cyberComplaint Gor Employee By id 
        Route::get('cybercomplaint/{id}',[EmployeeCyberComplaintsController::class,'show']);
    
        #### Update CyberComplaint Status
        Route::put('cybercomplaints/{id}',[EmployeeCyberComplaintsController::class,'updateStatus']);
    });
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();

    

});

