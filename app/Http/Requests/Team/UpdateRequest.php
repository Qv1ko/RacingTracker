<?php

declare(strict_types=1);

namespace App\Http\Requests\Team;

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
            'name' => ['required', Rule::unique('teams')->ignore($this->id), 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9][A-Za-z0-9\s\-&]*[A-Za-z0-9]$/'],
            'nationality' => ['nullable', 'max:25'],
            'status' => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'The name may only contain letters, numbers, spaces, hyphens and ampersands.',
            'name.unique' => 'The team already exists.',
        ];
    }
}
