<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::with('user')->get();
        $users = User::all();

        foreach ($reviews as $review) {
            $availableUsers = $users->where('id', '!=', $review->user_id);

            $likeCount = min(
                random_int(0, 3),
                $availableUsers->count()
            );

            if ($likeCount === 0) {
                continue;
            }

            $likedUserIds = $availableUsers
                ->random($likeCount)
                ->pluck('id');

            $review->likedUsers()->syncWithoutDetaching($likedUserIds);
        }
    }
}
