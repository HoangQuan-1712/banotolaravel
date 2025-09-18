<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // CHỈ cho sửa 'content'
        return [
            'content' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }
}
