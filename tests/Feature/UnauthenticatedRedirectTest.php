<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
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
    public function 未認証ユーザーが書籍を編集しようとするとログイン画面へリダイレクトされる(): void
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
    public function 未認証ユーザーがレビューを編集しようとするとログイン画面へリダイレクトされる(): void
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
    public function 未認証ユーザーがジャンルを編集しようとするとログイン画面へリダイレクトされる(): void
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
}
