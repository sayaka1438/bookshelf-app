<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

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
            1 => '期待していた内容とは異なりました。',
            2 => '少し物足りなさを感じました。',
            3 => '読みやすく、参考になりました。',
            4 => 'とても参考になり、満足しました。',
            5 => '非常に良い内容で、おすすめです。',
        ];

        foreach ($books as $book) {
            $reviewCount = fake()->numberBetween(2, 4);

            $reviewUsers = $users->random($reviewCount);

            foreach ($reviewUsers as $user) {
                $rating = fake()->numberBetween(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $comments[$rating],
                ]);
            }
        }
    }
}
