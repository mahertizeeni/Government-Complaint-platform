
<?php


use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;
use Illuminate\Http\Request;
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
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});




Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

