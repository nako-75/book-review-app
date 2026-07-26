<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;

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

            $user->favorites()->syncWithoutDetaching($bookIds);
        }
    }
}
