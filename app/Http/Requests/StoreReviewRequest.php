<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // đã đăng nhập
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'order_id'   => ['required', 'integer', 'exists:orders,id'],
            'rating'     => ['required', 'integer', 'min:1', 'max:5'],
            'content'    => ['required', 'string', 'min:5', 'max:5000'],
            'images'     => ['nullable', 'array', 'max:6'],        // tối đa 6 ảnh
            'images.*'   => ['image', 'mimes:jpg,jpeg,png,gif', 'max:5120'], // <= 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'images.max' => 'Bạn chỉ có thể tải lên tối đa 6 ảnh.',
            'images.*.image' => 'Tệp tải lên phải là ảnh.',
            'images.*.mimes' => 'Ảnh phải thuộc định dạng jpg, jpeg, png, hoặc gif.',
            'images.*.max' => 'Mỗi ảnh không vượt quá 5MB.',
        ];
    }
}
