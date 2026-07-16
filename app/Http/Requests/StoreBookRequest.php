<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|regex:/^\d{13}$/|unique:books,isbn',
            'published_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'image_url' => 'nullable|url|max:255',
            'genres' => 'required|array|min:1',
            'genres.*' => 'integer|exists:genres,id',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'author' => '著者名',
            'isbn' => 'ISBN',
            'published_date' => '出版日',
            'description' => '説明',
            'image_url' => '画像URL',
            'genres' => 'ジャンル',
        ];
    }

    public function messages(): array
    {
        return [
            'isbn.regex' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'ISBNはすでに登録されています。',
            'published_date.date' => '出版日は有効な日付を入力してください。',
            'image_url.url' => '画像URLはURL形式で入力してください。',
            'genres.required' => 'ジャンルを選択してください。',
            'genres.min' => 'ジャンルを選択してください。',
            'genres.*.integer' => '選択されたジャンルが正しくありません。',
            'genres.*.exists' => '選択されたジャンルが存在しません。',
        ];
    }
}
