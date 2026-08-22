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
    public function ランキングは最大10件まで表示できる(): void
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
        $lowRatedBook = Book::factory()->create();
        $highRatedBook = Book::factory()->create();

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

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($highRatedBook, $lowRatedBook) {
            return $rankedBooks->pluck('id')->all() === [
                $highRatedBook->id,
                $lowRatedBook->id,
            ];
        });
    }

    /** @test */
    public function 平均評価が同じ場合はレビュー件数の多い順に表示される(): void
    {
        $lessReviewedBook = Book::factory()->create();

        $moreReviewedBook = Book::factory()->create();

        Review::factory()->count(2)->create([
            'book_id' => $moreReviewedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $lessReviewedBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($moreReviewedBook, $lessReviewedBook) {
            return $rankedBooks->pluck('id')->all() === [
                $moreReviewedBook->id,
                $lessReviewedBook->id,
            ];
        });
    }

    /** @test */
    public function 平均評価・レビュー件数が同じ場合は新しい書籍順で表示される(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        Review::factory()->create([
            'book_id' => $oldBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $newBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($newBook, $oldBook) {
            return $rankedBooks->pluck('id')->all() === [
                $newBook->id,
                $oldBook->id,
            ];
        });
    }

    /** @test */
    public function 平均評価・レビュー件数・作成日時が同じ場合はタイトル順で表示される(): void
    {
        $createdAt = now();

        $secondBook = Book::factory()->create([
            'title' => 'かきくけこ',
            'created_at' => $createdAt,
        ]);

        $firstBook = Book::factory()->create([
            'title' => 'あいうえお',
            'created_at' => $createdAt,
        ]);

        Review::factory()->create([
            'book_id' => $secondBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $firstBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) use ($firstBook, $secondBook) {
            return $rankedBooks->pluck('id')->all() === [
                $firstBook->id,
                $secondBook->id,
            ];
        });
    }
}
