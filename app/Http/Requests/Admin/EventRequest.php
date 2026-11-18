<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use App\Support\BiodataOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'registration_open' => $this->boolean('registration_open'),
            'popup_enabled' => $this->boolean('popup_enabled'),
            'notify_all' => $this->boolean('notify_all'),
            'remove_image' => $this->boolean('remove_image'),
            'area' => $this->filled('area') ? $this->input('area') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'remove_image' => ['nullable', 'boolean'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'place' => ['nullable', 'string', 'max:200'],
            'join_url' => ['nullable', 'url:http,https', 'max:500'],
            'area' => ['nullable', Rule::in(array_keys(BiodataOptions::districts()))],
            'is_published' => ['nullable', 'boolean'],
            'registration_open' => ['nullable', 'boolean'],
            'popup_enabled' => ['nullable', 'boolean'],
            'reminder_interval_min' => ['required', 'integer', Rule::in(Event::INTERVALS)],
            'notify_all' => ['nullable', 'boolean'],
        ];
    }
}
