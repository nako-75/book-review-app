<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;
use App\Models\Genre;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_date' => '1905-01-01',
                'genres' => ['小説'],
                'description' => '明治の世を猫の視点からユーモラスかつ風刺的に描いた名作。',
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_date' => '1936-10-01',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人間関係の原則を説き、世界中で読み継がれる自己啓発のバイブル。',
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_date' => '2012-06-23',
                'genres' => ['技術書'],
                'description' => 'より良いコードを書くためのシンプルで実践的なテクニック集。',
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_date' => '2013-08-30',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人生とビジネスにおいて成功するためのパラダイムシフトを学ぶ。',
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_date' => '1906-04-01',
                'genres' => ['小説'],
                'description' => '無鉄砲で正義感あふれる青年教師が地方の学校で巻き起こす痛快劇。',
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_date' => '2016-09-08',
                'genres' => ['歴史', '科学'],
                'description' => '人類の誕生から現代までの歴史を壮大なスケールで解き明かす。',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_date' => '2017-12-18',
                'genres' => ['技術書'],
                'description' => '保守性の高いクリーンなコードを書くための必読書。',
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_date' => '2013-12-13',
                'genres' => ['自己啓発'],
                'description' => 'アドラー心理学を対話形式で分かりやすく解説したベストセラー。',
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_date' => '2015-03-11',
                'genres' => ['小説'],
                'description' => 'お笑い芸人の生き様と葛藤を描き、芥川賞を受賞した話題作。',
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_date' => '2019-01-11',
                'genres' => ['ビジネス', '科学'],
                'description' => '世界をデータに基づいて正しく見る習慣を身につける。',
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_date' => '2007-01-18',
                'genres' => ['ビジネス', '歴史'],
                'description' => '世界経済を大きく変えたコンテナ輸送の歴史とイノベーション。',
            ],
        ];

        foreach ($books as $index => $bookData) {
            $num = $index + 1;
            $genreNames = $bookData['genres'];
            unset($bookData['genres']);

            $bookData['user_id'] = $user->id;
            $bookData['image_url'] = "https://placehold.co/200x300/e2e8f0/475569?text={$num}";

            $book = Book::firstOrCreate(
                ['isbn' => $bookData['isbn']],
                $bookData
            );

            $genreIds = Genre::whereIn('name', $genreNames)->pluck('id');
            $book->genres()->sync($genreIds);
        }
    }
}
