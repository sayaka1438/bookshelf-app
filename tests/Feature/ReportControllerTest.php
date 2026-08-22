<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはマイ読書レポートを表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');

        $response->assertViewHas('stats');
    }

    /** @test */
    public function 自分のレビューだけを集計できる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Review::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        Review::factory()->count(3)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 2;
        });
    }

    /** @test */
    public function 読了冊数は完了した読書計画のみ集計できる(): void
    {
        $user = User::factory()->create();

        ReadingPlan::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['books_read'] === 2;
        });
    }

    /** @test */
    public function 平均評価を正しく集計できる(): void
    {
        $user = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['average_rating'] === 4.5;
        });
    }

    /** @test */
    public function 評価分布が1〜5で正しく集計される(): void
    {
        $user = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 1,
        ]);

        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'rating' => 2,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        Review::factory()->count(3)->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [1, 2, 0, 1, 3];
        });
    }

    /** @test */
    public function 高評価書籍を評価順で上位5件取得できる(): void
    {
        $user = User::factory()->create();

        Review::factory()->count(2)->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->count(4)->create([
            'user_id' => $user->id,
            'rating' => 4,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['top_rated_books']->count() === 5
                && $stats['top_rated_books']->pluck('rating')->all() === [
                    5,
                    5,
                    4,
                    4,
                    4,
                ];
        });
    }

    /** @test */
    public function 高評価書籍で評価が同じ場合は新しいレビュー順で表示される(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create();
        $newBook = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $oldBook->id,
            'rating' => 5,
            'created_at' => now()->subDay(),
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $newBook->id,
            'rating' => 5,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) use ($newBook, $oldBook) {
            return $stats['top_rated_books']->pluck('id')->all() === [
                $newBook->id,
                $oldBook->id,
            ];
        });
    }

    /** @test */
    public function ジャンル別評価傾向を平均評価順で上位5件取得できる(): void
    {
        $user = User::factory()->create();

        $genres = Genre::factory()->count(6)->create();

        $ratings = [5, 5, 4, 4, 3, 2];

        $genres->each(function ($genre, $index) use ($user, $ratings) {
            $book = Book::factory()->create();

            $book->genres()->attach($genre);

            Review::factory()->create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'rating' => $ratings[$index],
            ]);
        });

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['genre_ratings']->count() === 5
                && $stats['genre_ratings']->pluck('average_rating')->all() === [
                    5,
                    5,
                    4,
                    4,
                    3,
                ];
        });
    }

    /** @test */
    public function ジャンル別評価傾向で平均評価が同じ場合はレビュー件数順で表示される(): void
    {
        $user = User::factory()->create();

        $lessReviewedGenre = Genre::factory()->create();
        $moreReviewedGenre = Genre::factory()->create();

        $lessReviewedBook = Book::factory()->create();

        $moreReviewedBook1 = Book::factory()->create();
        $moreReviewedBook2 = Book::factory()->create();

        $lessReviewedBook->genres()->attach($lessReviewedGenre->id);

        $moreReviewedBook1->genres()->attach($moreReviewedGenre->id);
        $moreReviewedBook2->genres()->attach($moreReviewedGenre->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $lessReviewedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $moreReviewedBook1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $moreReviewedBook2->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) use ($moreReviewedGenre, $lessReviewedGenre) {
            return $stats['genre_ratings']->pluck('id')->all() === [
                $moreReviewedGenre->id,
                $lessReviewedGenre->id,
            ];
        });
    }

    /** @test */
    public function ジャンル別評価傾向で平均評価とレビュー件数が同じ場合は名前順で表示される(): void
    {
        $user = User::factory()->create();

        $secondGenre = Genre::factory()->create([
            'name' => 'かきくけこ',
        ]);
        $firstGenre = Genre::factory()->create([
            'name' => 'あいうえお',
        ]);

        $secondBook = Book::factory()->create();

        $firstBook = Book::factory()->create();

        $secondBook->genres()->attach($secondGenre->id);

        $firstBook->genres()->attach($firstGenre->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $secondBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $firstBook->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) use ($firstGenre, $secondGenre) {
            return $stats['genre_ratings']->pluck('id')->all() === [
                $firstGenre->id,
                $secondGenre->id,
            ];
        });
    }
}
