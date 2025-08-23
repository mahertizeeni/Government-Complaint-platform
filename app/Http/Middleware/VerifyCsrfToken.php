<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * المسارات التي سيتم استثناؤها من التحقق من CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*', 
        'sanctum/*'
    ];
}
