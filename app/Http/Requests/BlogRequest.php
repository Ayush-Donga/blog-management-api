<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ];
        }

        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    protected function prepareForValidation()
    {
        $data = [];

        if ($this->has('title') && $this->input('title') !== null) {
            $data['title'] = $this->input('title');
        }
        if ($this->has('description') && $this->input('description') !== null) {
            $data['description'] = $this->input('description');
        }

        $this->merge($data);
    }
}