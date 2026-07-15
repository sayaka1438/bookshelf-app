<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id', 'email');
        $books = Book::pluck('id', 'isbn');

        $favorites = [
            'yamada@example.com' => [
                '9784101010014',
                '9784873115658',
                '9784309226712',
            ],
            'suzuki@example.com' => [
                '9784422100524',
                '9784863940246',
                '9784478025819',
                '9784822289607',
            ],
            'tanaka@example.com' => [
                '9784101010021',
                '9784048930598',
                '9784163902302',
            ],
            'sato@example.com' => [
                '9784101010014',
                '9784309226712',
                '9784822251468',
                '9784822289607',
            ],
            'takahashi@example.com' => [
                '9784422100524',
                '9784873115658',
                '9784048930598',
                '9784478025819',
                '9784163902302',
            ],
        ];

        foreach ($favorites as $email => $isbnList) {
            $user = User::findOrFail($users[$email]);

            $bookIds = collect($isbnList)
                ->map(fn(string $isbn) => $books[$isbn]);

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}