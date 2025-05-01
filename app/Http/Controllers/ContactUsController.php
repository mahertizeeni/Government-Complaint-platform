<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ContactUsRequest;
use App\Models\ContactUs;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ContactUsRequest $request)
    {
       $data=$request->validated();
       $recorde=ContactUs::create($data);
       if($recorde)
       {return ApiResponse::sendResponse(201,'sent successfully',null);}
    }
}
