<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $category=Category::all();
        if(count($category) > 0)
        {
            return ApiResponse::sendResponse(200,'the category of complaint',CategoryResource::Collection($category));
        }
        return ApiResponse::sendResponse(200,'no categoury to show',[]);
    }
}
