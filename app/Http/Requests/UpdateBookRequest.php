<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
        $bookId = $this->route('book')->id ?? $this->route('book');

        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'nullable',
                'string',
                'digits_between:10,13',
                Rule::unique('books', 'isbn')->ignore($bookId),
            ],
            'published_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必ず入力してください。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名は必ず入力してください。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.string' => 'ISBNは文字列で入力してください。',
            'isbn.digits_between' => 'ISBNは10桁または13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',
            'published_date.date' => '有効な日付形式で入力してください。',
            'description.string' => '説明は文字列で入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.exists' => '選択されたジャンルが無効です。',
            'image_url.url' => '有効なURL形式で入力してください。',
            'image_url.max' => '画像URLは255文字以内で入力してください。',
        ];
    }
}
