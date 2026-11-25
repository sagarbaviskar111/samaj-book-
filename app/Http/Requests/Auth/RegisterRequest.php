<?php

namespace App\Http\Requests\Auth;

use App\Support\BiodataOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone'));
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        $this->merge(['phone' => $phone, 'email' => $this->input('email') ?: null]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'gotra' => ['nullable', 'string', 'max:100'],
            'state' => ['required', Rule::in(array_keys(BiodataOptions::states()))],
            'district' => ['required', Rule::in(array_keys(BiodataOptions::districts()))],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('auth.phone_invalid'),
            'phone.unique' => __('auth.phone_taken'),
            'consent.accepted' => __('auth.consent_required'),
        ];
    }
}
