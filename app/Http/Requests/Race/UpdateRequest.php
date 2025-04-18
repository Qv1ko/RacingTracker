<?php

namespace App\Http\Requests\Race;

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
                'regex:/^[A-Za-z0-9 ]*$/',
                Rule::unique('races')->ignore($this->id)->where(function ($query) {
                    return $query->where('date', request('date'));
                })
            ],
            'date' => ['required', 'date'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters and numbers.',
            'name.unique' => 'The race already exists.',
            'date.date' => 'The date is invalid.',
        ];
    }
}
