<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['auto_approve' => $this->boolean('auto_approve')]);
    }

    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:80'],
            'site_tagline' => ['nullable', 'string', 'max:160'],
            'interest_daily_limit' => ['required', 'integer', 'min:1', 'max:200'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'auto_approve' => ['nullable', 'boolean'],
        ];
    }
}
