<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return ['Laravel' => app()->version()];
    return 'hello from admin page';
});

require __DIR__.'/auth.php';
