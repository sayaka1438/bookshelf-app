<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'rating' => 3,
            'comment' => 'テストコメント',
        ];

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $data);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    /** @test */
    public function レビュー投稿時に評価が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => '',
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function レビュー投稿時に評価が文字列だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => '不正な値',
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function レビュー投稿時に評価が0だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 0,
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function レビュー投稿時に評価が1だと投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'rating' => 1,
            'comment' => 'テストコメント',
        ];

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $data);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    /** @test */
    public function レビュー投稿時に評価が5だと投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'rating' => 5,
            'comment' => 'テストコメント',
        ];

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $data);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    /** @test */
    public function レビュー投稿時に評価が6だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 6,
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function レビュー投稿時にコメントが未入力でも投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'rating' => 3,
            'comment' => '',
        ];

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $data);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => null,
        ]);
    }

    /** @test */
    public function レビュー投稿時にコメントは1000文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $data = [
            'rating' => 3,
            'comment' => str_repeat('あ', 1000),
        ];

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), $data);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'レビューを投稿しました。');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    /** @test */
    public function レビュー投稿時にコメントが1001文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 3,
                'comment' => str_repeat('あ', 1001),
            ]);

        $response->assertSessionHasErrors('comment');
    }

    /** @test */
    public function 同一ユーザーが同じ書籍に2件目のレビューをするとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 3,
                'comment' => 'テストコメント',
            ]);

        $response->assertSessionHasErrors([
            'rating' => 'この書籍にはすでにレビューを投稿しています。',
        ]);

        $this->assertDatabaseCount('reviews', 1);
    }

    /** @test */
    public function 認証済みユーザーは自分のレビューの編集画面を表示できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertOk();
        $response->assertViewIs('reviews.edit');

        $response->assertViewHas('review', $review);
    }

    /** @test */
    public function 他人が投稿したレビューの編集画面にアクセスすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    /** @test */
    public function 存在しないレビューidで編集画面にアクセスすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reviews.edit', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは自分の投稿したレビューを更新できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => '更新前のコメント',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 3,
                'comment' => '更新後のコメント',
            ]);

        $response->assertRedirect(route('books.show', $review->book));
        $response->assertSessionHas('success', 'レビューを更新しました。');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 3,
            'comment' => '更新後のコメント',
        ]);
    }

    /** @test */
    public function 他人が投稿したレビューを更新しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'comment' => '更新前のコメント',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 3,
                'comment' => '更新後のコメント',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'comment' => '更新前のコメント',
        ]);
    }

    /** @test */
    public function 存在しないレビューidで更新しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('reviews.update', 999999), [
                'rating' => '5',
            ]);

        $response->assertNotFound();
    }

    /** @test */
    public function レビュー更新時に評価が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
            'comment' => '更新前のコメント',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => '',
                'comment' => '更新後のコメント',
            ]);

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => '更新前のコメント',
        ]);
    }

    /** @test */
    public function レビュー更新時に評価が不正な値だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
            'comment' => '更新前のコメント',
        ]);

        $response = $this->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 99,
                'comment' => '更新後のコメント',
            ]);

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => '更新前のコメント',
        ]);
    }

    /** @test */
    public function 認証済みユーザーは自分の投稿したレビューを削除できる(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $review->book));
        $response->assertSessionHas('success', 'レビューを削除しました。');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function 他人が投稿したレビューを削除しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function 存在しないレビューidで削除しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('reviews.destroy', 999999));

        $response->assertNotFound();
    }
}
