<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\User;

class UserController extends Controller
{
        public function index()
    {
        $users = User::all();
        return ApiResponse::sendResponse(200, 'All users fetched successfully', $users);
    }

     public function destroy(User $user)
    {
        $user->delete();
        return ApiResponse::sendResponse(200, 'User deleted successfully');
    }
}
