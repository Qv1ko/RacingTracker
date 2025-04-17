<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\'\.\- ]+$/u',
                Rule::unique('drivers')->where(function ($query) {
                    return $query->where('surname', request('surname'));
                })
            ],
            'surname' => ['required', 'string', 'max:255', 'regex:/^[\p{L}\'\.\- ]+$/u'],
            'nationality' => ['max:255'],
            'status' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, apostrophes, and hyphens.',
            'name.unique' => 'The driver already exists.',
            'surname.regex' => 'The surname may only contain letters, spaces, apostrophes, and hyphens.',
        ];
    }
}
