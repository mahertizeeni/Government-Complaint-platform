<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeRegisterRequest extends FormRequest
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
             'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'password' => ['required', 'confirmed',
          Password::min(8)       // الطول الأدنى 8
            ->letters()       // لازم يحتوي أحرف
            ->mixedCase()     // لازم يحتوي حرف كبير وصغير
            ->numbers()       // لازم يحتوي أرقام
            ->symbols(),      // لازم يحتوي رموز
        ],
            'government_entity' => ['required'],
            'city'=>['required'],
        ];
    }
}
