<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return ['Laravel' => app()->version()];
// });
Route::get('/', function () {
    
    require __DIR__.'/auth.php';
    return 'Hello World';});

