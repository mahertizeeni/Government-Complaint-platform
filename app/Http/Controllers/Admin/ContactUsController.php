<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class ContactUsController extends Controller
{
    public function index()
{
    $messages = ContactUs::latest()->get();
    return ApiResponse::sendResponse(200, 'Fetched contact messages', $messages);
}

}
