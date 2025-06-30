<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ContactUsRequest;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function __invoke(ContactUsRequest $request)
    {
       $data=$request->validated();
       $record=ContactUs::create($data);
       if($record)
       {return ApiResponse::sendResponse(201,'sent successfully',null);}
    }
}
