<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ゲストユーザーはランキング一覧表示できる(): void
    {
        $book = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');

        $response->assertViewHas('rankedBooks');
    }

    /** @test */
    public function レビューがない書籍はランキングに含まれない(): void
    {
        $bookWithReview = Book::factory()->create();
        $bookWithoutReview = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $bookWithReview->id,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($bookWithReview, $bookWithoutReview) {
            return
                $rankedBooks->contains('id', $bookWithReview->id)
                &&
                ! $rankedBooks->contains('id', $bookWithoutReview->id);
        });
    }

    /** @test */
    public function ランキングは最大10件まで表示される(): void
    {
        $books = Book::factory()->count(11)->create();

        foreach ($books as $book) {
            Review::factory()->create([
                'book_id' => $book->id,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }

    /** @test */
    public function ランキングは平均評価の高い順に表示される(): void
    {
        $highRatedBook = Book::factory()->create();
        $lowRatedBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 3,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($highRatedBook) {
            return $rankedBooks->first()->id === $highRatedBook->id;
        });
    }
}
