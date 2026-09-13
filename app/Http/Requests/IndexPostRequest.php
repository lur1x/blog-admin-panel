<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'sort' => [
                'sometimes',
                'string',
                Rule::in(['date_asc', 'date_desc', 'title_asc', 'title_desc']),
            ],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'offset' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort' => $this->input('sort', 'date_desc'),
            'limit' => $this->input('limit', 15),
            'offset' => $this->input('offset', 0),
        ]);
    }
}
