<?php

declare(strict_types=1);

namespace App\Http\Requests\Race;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9 ]*$/',
                Rule::unique('races')->ignore($this->id)->where(function ($query) {
                    return $query->where('date', request('date'));
                }),
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
