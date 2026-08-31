<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateReadingPlanRequest extends FormRequest
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
            'target_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function attributes(): array
    {
        return [
            'target_date' => '期日',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('target_date')) {
                    return;
                }

                $plan = $this->route('plan');

                $alreadyExists = ReadingPlan::where('user_id', auth()->id())
                    ->where('book_id', $plan->book_id)
                    ->where('status', ReadingPlanStatus::InProgress)
                    ->where('id', '!=', $plan->id)
                    ->exists();

                if ($alreadyExists) {
                    $validator->errors()->add(
                        'target_date',
                        'この書籍は既に進行中の読書計画が存在します。'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'target_date.required' => '期日は必須です。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }
}
