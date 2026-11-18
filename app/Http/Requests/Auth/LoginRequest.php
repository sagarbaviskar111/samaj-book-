<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        $this->merge(['phone' => $phone]);
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:15'],
            'password' => ['required', 'string'],
        ];
    }
}
