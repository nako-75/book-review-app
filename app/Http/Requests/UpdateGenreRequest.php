<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGenreRequest extends FormRequest
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
        $genreId = $this->route('genre');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:genres,name,' . $genreId
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名は必須です。',
            'name.string' => 'ジャンル名は文字列で入力してください。',
            'name.unique' => 'そのジャンル名は既に登録されています。',
            'name.max' => 'ジャンル名は255文字以内で入力してください。',
        ];
    }
}
