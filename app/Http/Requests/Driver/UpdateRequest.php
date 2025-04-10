<?php

namespace App\Http\Requests\Driver;

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
            'name' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\'\.\- ]+$/u'],
            'surname' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\'\.\- ]+$/u'],
            'nationality' => ['string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, apostrophes, and hyphens.',
            'surname.regex' => 'The surname may only contain letters, spaces, apostrophes, and hyphens.',
        ];
    }
}
