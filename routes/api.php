<?php

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
