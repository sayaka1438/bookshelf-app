<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function レビューに設定したfillableを確認できる(): void
    {
        $review = new Review;

        $this->assertEquals([
            'user_id',
            'book_id',
            'rating',
            'comment',
        ], $review->getFillable());
    }

    /** @test */
    public function レビューが特定のユーザーに属している(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($review->user->is($user));
    }

    /** @test */
    public function レビューが特定の書籍に属している(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertTrue($review->book->is($book));
    }

    /** @test */
    public function レビューが複数のいいねを持つ(): void
    {
        $review = Review::factory()->create();
        $reviewLikes = ReviewLike::factory()
            ->count(3)
            ->create([
                'review_id' => $review->id,
            ]);

        $this->assertCount(3, $review->reviewLikes);
        $this->assertTrue(
            $review->reviewLikes->contains($reviewLikes->first())
        );
    }
}
