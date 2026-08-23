<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
        $bookId = $this->route('book');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'isbn' => [
                'sometimes',
                'required',
                'string',
                'digits_between:10,13',
                Rule::unique('books', 'isbn')->ignore($bookId)
            ],
            'published_date' => ['sometimes', 'required', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'genres' => ['sometimes', 'required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'user_id' => ['sometimes', 'required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.required' => 'ISBNは必ず入力してください。',
            'isbn.string' => 'ISBNは文字列で入力してください。',
            'isbn.digits_between' => 'ISBNは10桁または13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに他の書籍で登録されています。',
            'published_date.required' => '出版日を入力してください。',
            'published_date.date' => '有効な日付形式で入力してください。',
            'description.string' => '説明は文字列で入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.min' => 'ジャンルを1つ以上選択してください。',
            'genres.*.exists' => '指定されたジャンルが存在しません。',
            'image_url.url' => '有効なURL形式で入力してください。',
            'image_url.max' => '画像URLは255文字以内で入力してください。',
            'user_id.required' => '登録者IDを入力してください。',
            'user_id.integer' => '登録者IDは整数で入力してください。',
            'user_id.exists' => '指定された登録者IDが存在しません。',
        ];
    }
}