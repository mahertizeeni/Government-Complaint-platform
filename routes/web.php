<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return ['Laravel' => app()->version()];
    return view ('welcome');
});

require __DIR__.'/auth.php';
