<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Book;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        $comments = [
            'とても読みやすくて勉強になりました！',
            '内容が深く、何回も読み返したい一冊です。',
            '視点が変わる素晴らしい書籍でした。',
            '初心者にもわかりやすくおすすめです。',
            '実務ですぐに活かせる知識が詰まっています。',
            'ストーリーに引き込まれました。',
            '現代人プレビューの書だと思います。',
            '考えさせられる内容で非常に有意義でした。',
        ];

        foreach ($books as $bookIndex => $book) {
            $reviewCount = ($bookIndex % 3) + 2;

            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users[($bookIndex + $i) % $users->count()];

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => rand(3, 5),
                    'comment' => $comments[($bookIndex + $i) % count($comments)],
                ]);
            }
        }
    }
}
