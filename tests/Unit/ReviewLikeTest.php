<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューいいねに設定したfillableを確認できる(): void
    {
        $reviewLike = new ReviewLike;

        $this->assertEquals([
            'user_id',
            'review_id',
        ], $reviewLike->getFillable());
    }

    /** @test */
    public function レビューいいねが特定のユーザーに属している(): void
    {
        $user = User::factory()->create();
        $reviewLike = ReviewLike::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($reviewLike->user->is($user));
    }

    /** @test */
    public function レビューいいねが特定のレビューに属している(): void
    {
        $review = Review::factory()->create();
        $reviewLike = ReviewLike::factory()->create([
            'review_id' => $review->id,
        ]);

        $this->assertTrue($reviewLike->review->is($review));
    }
}
