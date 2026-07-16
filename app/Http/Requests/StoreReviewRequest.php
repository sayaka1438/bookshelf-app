<?php

namespace App\Http\Requests;

use App\Models\Review;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReviewRequest extends FormRequest
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
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $book = $this->route('book');

                $alreadyReviewed = Review::where('user_id', auth()->id())
                    ->where('book_id', $book->id)
                    ->exists();

                if ($alreadyReviewed) {
                    $validator->errors()->add(
                        'rating',
                        'この書籍にはすでにレビューを投稿しています。'
                    );
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'rating' => '評価',
            'comment' => 'コメント',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.integer' => '評価が正しくありません。',
            'rating.between' => '評価は１〜５で入力してください。',
        ];
    }
}
