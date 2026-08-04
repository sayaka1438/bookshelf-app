<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'genre' => 'nullable|integer|exists:genres,id',
            'sort' => [
                'nullable',
                Rule::in(['newest', 'oldest', 'rating', 'title']),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'keyword' => 'キーワード',
            'genre' => 'ジャンル',
            'sort' => '並び順',
        ];
    }

    public function messages(): array
    {
        return [
            'genre.exists' => '指定されたジャンルは存在しません。',
            'sort.in' => '並び順の指定が不正です。',
        ];
    }
}
