<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * ユーザーが書籍を登録できるかどうかを判定する。
     */
    public function create(User $user): bool
    {
        return $user !== null;
    }

    /**
     * ユーザーが指定の書籍を更新できるかどうかを判定する。
     */
    public function update(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }

    /**
     * ユーザーが指定の書籍を削除できるかどうかを判定する。
     */
    public function delete(User $user, Book $book): bool
    {
        return $user->id === $book->user_id;
    }
}
