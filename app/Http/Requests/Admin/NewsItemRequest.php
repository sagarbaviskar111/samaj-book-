<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'show_on_landing' => $this->boolean('show_on_landing'),
            'notify_all' => $this->boolean('notify_all'),
            'remove_image' => $this->boolean('remove_image'),
        ]);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['news', 'poster', 'event'])],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remove_image' => ['nullable', 'boolean'],
            'event_at' => ['nullable', 'date', 'required_if:type,event'],
            'event_place' => ['nullable', 'string', 'max:200'],
            'is_published' => ['nullable', 'boolean'],
            'show_on_landing' => ['nullable', 'boolean'],
            'notify_all' => ['nullable', 'boolean'],
        ];
    }
}
