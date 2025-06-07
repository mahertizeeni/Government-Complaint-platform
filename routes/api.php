<?php

// use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CyberComplaintController;
use App\Http\Controllers\employee\auth\AuthController;
use App\Http\Controllers\EmployeeComplaintsController;
use App\Http\Controllers\EmployeeCyberComplaintsController;
use App\Http\Controllers\SmartChatController;
use App\Http\Controllers\UserComplaintController;
use App\Models\ContactUs;
use Illuminate\Support\Facades\Http;

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
######### Complaint Recource Endpoint
Route::apiResource('User-Complaints',UserComplaintController::class)->only('index', 'store', 'show', 'destroy');
######### CyberComplaint Endpoint
Route::post('/cybercomplaint',[CyberComplaintController::class,'store']);

Route::controller(AuthController::class)->group(function(){
    
});
######## Employee account
Route::prefix('employee')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        #### middelware for making sure of logging in
        Route::post('logout', [AuthController::class, 'logout']);
        #### Get Complaints Gor Employee 
        Route::get('complaints',[EmployeeComplaintsController::class,'getComplaints']);
        Route::get('Cybercomplaints',[EmployeeCyberComplaintsController::class,'getComplaints']);
        #### Update Status
        Route::put('complaints/{id}/status',[EmployeeComplaintsController::class,'updateStatus']);
    });
});

// Route::get('/test-groq-api', function () {
//     $response = Http::withHeaders([
//         'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
//     ])->post('https://api.groq.com/openai/v1/chat/completions', [
//         'model' => 'allam-2-7b',
//         'messages' => [
//             ['role' => 'system', 'content' => 'test'],
//             ['role' => 'user', 'content' => 'Hello']
//         ]
//     ]);

//     return [
//         'status' => $response->status(),
//         'body' => $response->body(),
//     ];

//     });



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();

    
// use Illuminate\Support\Facades\Route;
});

