<?php

namespace App\Http\Requests\Race;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
                'max:50',
                'regex:/^[A-Za-z0-9 ]*$/',
                Rule::unique('races')->where(function ($query) {
                    return $query->where('date', request('date'));
                })
            ],
            'date' => ['required',  'date'],
            'result.*.position' => ['required', 'string'],
            'result.*.driver' => ['required', 'exists:drivers,id'],
            'result.*.team' => ['nullable', 'exists:teams,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters and numbers.',
            'name.unique' => 'The race already exists.',
            'date.date' => 'The date is invalid.',
            'result.*.position.required' => 'The position is required.',
            'result.*.position.string'   => 'The position must be a string.',
            'result.*.driver.required' => 'The driver is required.',
            'result.*.driver.exists'   => 'The driver not exists.',
            'result.*.team.exists'   => 'The team not exists.',
        ];
    }
}
