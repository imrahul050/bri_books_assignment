<?php

namespace App\Http\Requests\Book;

use App\Traits\ApiResponser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{
    use ApiResponser;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'          => 'sometimes|required|string|max:255',
            'author'         => 'sometimes|required|string|max:255',
            'cover_image'    => 'sometimes|nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'price'          => 'sometimes|required|numeric|min:0',
            'published_date' => 'sometimes|required|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'             => 'The title field is required.',
            'author.required'            => 'The author field is required.',
            'cover_image.image'          => 'The cover image must be an image file.',
            'cover_image.mimes'          => 'The cover image must be jpeg, jpg, png or webp.',
            'cover_image.max'            => 'The cover image must not exceed 2MB.',
            'price.required'             => 'The price field is required.',
            'price.numeric'              => 'The price must be a number.',
            'published_date.required'    => 'The published date field is required.',
            'published_date.date_format' => 'The published date must be in Y-m-d format.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->wantsJson()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                $this->errorResponse(
                    'Validation failed.',
                    $validator->errors()->first(),
                    422
                )
            );
        }

        parent::failedValidation($validator);
    }
}
