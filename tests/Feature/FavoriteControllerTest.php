<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーは自分のお気に入り書籍を取得でき、ページネーションされる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Favorite::factory()->count(15)->create([
            'user_id' => $user->id,
        ]);

        Favorite::factory()->count(5)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertOk();
        $response->assertViewIs('favorites.index');

        $response->assertViewHas(
            'books',
            function ($books) {
                return $books->count() === 10
                    && $books->total() === 15;
            }
        );
    }

    /** @test */
    public function 認証済みユーザーはお気に入り追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'お気に入りに追加しました。');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーはお気に入り解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Favorite::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', 'お気に入りを解除しました。');

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
