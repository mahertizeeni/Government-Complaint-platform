<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Admin|null $admin */
        $admin = auth('admin')->user();

        if ($admin) {
            return $next($request);
        }

        return ApiResponse::sendResponse(403, 'Unauthorized. Admins only.', []);
    }
}
