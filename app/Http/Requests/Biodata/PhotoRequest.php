<?php

namespace App\Http\Requests\Biodata;

use Illuminate\Foundation\Http\FormRequest;

class PhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:1', 'max:6'],
            'photos.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return ['photos.*.max' => 'हर फ़ोटो 4 MB से छोटी होनी चाहिए।', 'photos.*.image' => 'सिर्फ़ फ़ोटो (JPG, PNG, WebP) चुनिए।'];
    }
}
