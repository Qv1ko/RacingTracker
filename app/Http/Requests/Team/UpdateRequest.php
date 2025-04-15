<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255', 'regex:/^(?i)[A-Z0-9][A-Z0-9\s\-&]*[A-Z0-9]$/'],
            'nationality' => ['max:255'],
            'status' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters, numbers, spaces, hyphens and ampersands.',
        ];
    }
}
