<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SuggestionResource;

class SuggestionController extends Controller
{
    public function index()
    {
        $suggestions = Suggestion::where('user_id',Auth::id())->get();
        if ($suggestions->count() > 0) {
            return ApiResponse::sendResponse(200, 'List of suggestions', SuggestionResource::collection($suggestions));
        }
        return ApiResponse::sendResponse(200, 'No suggestions found', []);
    }

    // إنشاء مقترح جديد
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'government_entity_id' => 'required|exists:government_entities,id',
            'city_id' => 'required|exists:cities,id',
        ]);

        $suggestion = Suggestion::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'government_entity_id' => $request->government_entity_id,
            'city_id' => $request->city_id,
        ]);

        return ApiResponse::sendResponse(201, 'Suggestion created successfully', new SuggestionResource($suggestion));
    }

    // عرض مقترح معين
    public function show($id)
    {
        $suggestion = Suggestion::findOrFail($id);

        return ApiResponse::sendResponse(
            200,
            'Suggestion fetched successfully',
            new SuggestionResource($suggestion)
        );
    }
    public function update(Request $request, Suggestion $suggestion)
    {
        

    }

    // حذف مقترح
    public function destroy($id)
    {
        $suggestion = Suggestion::findOrFail($id);
        $suggestion->delete();
        return ApiResponse::sendResponse(200,'Suggestion Deleted Successfully',[]);
    }

    
}
