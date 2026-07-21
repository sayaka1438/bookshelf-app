<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'genre_id' => 'nullable|integer|exists:genres,id',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function attributes(): array
    {
        return [
            'keyword' => 'キーワード',
            'genre_id' => 'ジャンル',
            'page' => 'ページ番号',
            'per_page' => '1ページあたりの件数',
        ];
    }

    public function messages(): array
    {
        return [
            'genre_id.exists' => '指定されたジャンルは存在しません。',
            'page.min' => 'ページ番号は1以上の数字を指定してください。',
            'per_page.min' => '1ページあたりの件数は1件以上で入力してください。',
            'per_page.max' => '1ページあたりの件数は100件以内で入力してください。',
        ];
    }
}
