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

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        $comments = [
            5 => [
                '非常に素晴らしい内容でした！何度も読み返したい一冊です。',
                '実務ですぐに活かせる知識が詰まっています。',
            ],
            4 => [
                'とてもタメになる良書でした。おすすめできます。',
                '初心者にもわかりやすくおすすめです。',
            ],
            3 => [
                '標準的で読みやすい内容でした。普通に楽しめます。',
                '内容が深く、参考にはなりました。',
            ],
            2 => [
                '少し期待外れな部分もありましたが、何とか読み終えました。',
                'もう少し具体的な解説が欲しかったです。',
            ],
            1 => [
                '自分にはあまり合いませんでした。',
                '期待していた内容とは少し異なっていました。',
            ],
        ];

        foreach ($books as $bookIndex => $book) {
            $reviewCount = ($bookIndex % 3) + 2;

            for ($i = 0; $i < $reviewCount; $i++) {
                $user = $users[($bookIndex + $i) % $users->count()];

                $rating = rand(1, 5);

                $ratingComments = $comments[$rating];
                $comment = $ratingComments[($bookIndex + $i) % count($ratingComments)];

                Review::create([
                    'book_id' => $book->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]);
            }
        }
    }
}
