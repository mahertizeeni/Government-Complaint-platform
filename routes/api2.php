<?php

use Illuminate\Http\Request;
use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\UserComplaintController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\ContactUsController as AdminContactUsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CyberComplaintController;

/* Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
}); */
Route::controller(AuthController::class)->group(function()
{
    Route::post('register','register');
    Route::post('login','login');
    Route::post('logout','logout')->middleware('auth:sanctum');
    Route::post('resetpassword','sendResetLink');
});
//=============Ananymous Api
Route::middleware('auth:sanctum')->group(function () {
    Route::get('complaints/anonymous', [UserComplaintController::class, 'getAnonymousComplaints']);
    Route::post('complaints/anonymous', [UserComplaintController::class, 'store']);
});

//=============Suggestion Api
Route::middleware('auth:sanctum')->prefix('Suggestion')->controller(SuggestionController::class)->group(function()
{
    Route::get('/','index');
    Route::post('/','store');

});
//==============]Admin Login Api
Route::prefix('admin')->controller(AdminAuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
});
//==============]Dashboard Api
Route::prefix('admin')->middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    // لوحات التحكم
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/suggestions', [DashboardController::class, 'suggestions'])->name('admin.dashboard.suggestions');
    Route::get('/dashboard/complaints', [DashboardController::class, 'complaints'])->name('admin.dashboard.complaints');

    // إدارة الموظفين
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

    Route::delete('/suggestions/{suggestion}', [SuggestionController::class, 'destroy']);

    Route::get('/dashboard/users', [UserController::class, 'index']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // حذف شكوى عادية
    Route::delete('/complaints/{id}', [userComplaintController::class, 'destroy']);

    // حذف شكوى إلكترونية
    Route::delete('/cyber-complaints/{id}', [CyberComplaintController::class, 'destroy']);
    Route::get('/contact-us', [AdminContactUsController::class, 'index']);



});
