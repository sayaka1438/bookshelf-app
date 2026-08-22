<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnauthenticatedRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーが書籍登録画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('books.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが書籍を登録しようとするとログイン画面へリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->post(route('books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが書籍編集画面にアクセスするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.edit', $book));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが書籍を更新しようとするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->put(route('books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが書籍を削除しようとするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->delete(route('books.destroy', $book));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがレビューを投稿しようとするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book), [
            'rating' => 3,
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがレビュー編集画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $review = Review::factory()->create();

        $response = $this->get(route('reviews.edit', $review));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがレビューを更新しようとするとログイン画面へリダイレクトされる(): void
    {
        $review = Review::factory()->create();

        $response = $this->put(route('reviews.update', $review), [
            'rating' => 3,
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがレビューを削除しようとするとログイン画面へリダイレクトされる(): void
    {
        $review = Review::factory()->create();

        $response = $this->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンル一覧画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンル詳細画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンル登録画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンルを登録しようとするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => 'テストジャンル名',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンル編集画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.edit', $genre));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンルを更新しようとするとログイン画面へリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->put(route('genres.update', $genre), [
            'name' => '更新後のジャンル名',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがジャンルを削除しようとするとログイン画面へリダイレクトされる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがお気に入り一覧画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがお気に入りを追加しようとするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがレビューにいいねしようとするとログイン画面へリダイレクトされる(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがマイ読書レポートにアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画一覧画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画作成画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('reading-plans.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画を作成しようとするとログイン画面へリダイレクトされる(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => today()->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画編集画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->get(route('reading-plans.edit', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画を更新しようとするとログイン画面へリダイレクトされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->put(route('reading-plans.update', $readingPlan), [
            'target_date' => today()->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画を削除しようとするとログイン画面へリダイレクトされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが読書計画を完了しようとするとログイン画面へリダイレクトされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create();

        $response = $this->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが通知一覧画面へアクセスするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーが通知を既読にしようとするとログイン画面へリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification($readingPlan, 'on_due_date')
        );

        $notification = $user->notifications()->first();

        $response = $this->post(route('notifications.read', $notification));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがisbn検索をするとログイン画面へリダイレクトされる(): void
    {
        $response = $this->get(route('books.isbn.search', [
            'isbn' => '9784101010014',
        ]));

        $response->assertRedirect(route('login'));
    }
}
