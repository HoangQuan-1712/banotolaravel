<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRespondReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // kiểm tra ở controller/policy (is_admin)
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'response' => ['required', 'string', 'min:3', 'max:5000'],
        ];
    }
}
