<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        foreach ($users as $index => $user) {
            $favoriteCount = ($index % 3) + 3;
            $userBooks = $books->random($favoriteCount);
            $bookIds = $userBooks->pluck('id')->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
