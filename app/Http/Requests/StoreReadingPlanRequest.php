<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
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
            'book_id' => [
                'required',
                'exists:books,id',
                function ($attribute, $value, $fail) {
                    $exists = ReadingPlan::where('user_id', $this->user()->id)
                        ->where('book_id', $value)
                        ->where('status', ReadingPlanStatus::Reading)
                        ->exists();

                    if ($exists) {
                        $fail('この書籍の読書計画はすでに登録されています。');
                    }
                },
            ],

            'target_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '指定された書籍は存在しません。',
            'target_date.required' => '目標日を入力してください。',
            'target_date.date' => '目標日は正しい日付形式で入力してください。',
            'target_date.after_or_equal' => '目標日は今日以降の日付を指定してください。',
        ];
    }
}
