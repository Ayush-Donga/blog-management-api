<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We'll handle auth in controller
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ];

        if ($this->method() === 'POST') { // Create requires image
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
        } else { // Edit optional
            $rules['image'] = 'sometimes|image|mimes:jpeg,png,jpg|max:2048';
        }

        return $rules;
    }
}