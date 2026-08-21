<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはレビューにいいねを追加できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));
        $response->assertSessionHas('success', 'いいねしました。');

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーはレビューのいいねを解除できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create();

        ReviewLike::factory()->create([
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book));
        $response->assertSessionHas('success', 'いいねを解除しました。');

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
