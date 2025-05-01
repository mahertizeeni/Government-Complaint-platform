<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ComplaintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
}); */
Route::get('/user', function (Request $request) {
    return 'hello from api 2 ';
});
Route::controller(AuthController::class)->group(function()
{
    Route::post('register','register');
    Route::post('login','login');
    Route::post('logout','logout')->middleware('auth:sanctum');
    Route::post('resetpassword','sendResetLink');
});
/* Route::prefix('Complaint')->controller(ComplaintController::class)->group(function()
{
    Route::get('/','index');

}); */
