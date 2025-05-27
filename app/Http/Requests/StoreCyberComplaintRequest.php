<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCyberComplaintRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'=>'required|in:انتحال شخصية,ابتزاز,احتيال,اختراق',
            'description'=>'required|string',
            'evidence_file'=>'nullable|file|mimes:png,jpg,pdf,jpeg|max:2048',
            'related_link'=>'nullable|url',
        ];
    }
}
