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

/* Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
}); */


Route::get('/user', function (Request $request) {
    return 'hello from api ';
});
Route::post('/chatai', [ComplaintChatController::class, 'handleChat']);


########## ChatBot Endpoint
Route::middleware('web')->post('/chat', [SmartChatController::class, 'chat']);
Route::get('/chatr', [SmartChatController::class, 'resetChat']);

######### ContactUs Endpoint
Route::post('/contactus',[ContactUsController::class, '__invoke']);
######### Complaint Recource Endpoint
Route::apiResource('User-Complaints',UserComplaintController::class)->only('index', 'store', 'show', 'destroy');
######### CyberComplaint Endpoint
// Route::post('/cybercomplaint',[CyberComplaintController::class,'store']);
Route::apiResource('User-CyberComplaint',CyberComplaintController::class)->only('index', 'store', 'show', 'destroy');

######## Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        #### middelware for making sure of logging in
        Route::post('logout', [AuthController::class, 'logout']);
        #### Get Complaints Gor Employee 
        Route::get('complaints',[EmployeeComplaintsController::class,'getComplaints']);
        #### Get Suggestion Gor Employee 
        Route::get('Suggestion',[EmployeeSuggestionController::class,'getSuggestions']);
        #### Get Cybercomplaints Gor Employee 
        Route::get('Cybercomplaints',[EmployeeCyberComplaintsController::class,'getComplaints']);
        #### Update Status
        Route::put('complaints/{id}/status',[EmployeeComplaintsController::class,'updateStatus']);
    });
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();

    

});

