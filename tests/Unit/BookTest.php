<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 書籍に設定したfillableを確認できる(): void
    {
        $book = new Book;

        $this->assertEquals([
            'title',
            'author',
            'isbn',
            'published_date',
            'description',
            'image_url',
            'user_id',
        ], $book->getFillable());
    }

    /** @test */
    public function 書籍が特定のユーザーに属している(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($book->user->is($user));
    }

    /** @test */
    public function 書籍に複数のジャンルを紐付けられる(): void
    {
        $book = Book::factory()->create();
        $genres = Genre::factory()->count(3)->create();

        $book->genres()->sync($genres->pluck('id'));

        $this->assertCount(3, $book->genres);
        $this->assertTrue(
            $book->genres->contains($genres->first())
        );
    }

    /** @test */
    public function 書籍が複数のレビューを持つ(): void
    {
        $book = Book::factory()->create();
        $reviews = Review::factory()
            ->count(3)
            ->create([
                'book_id' => $book->id,
            ]);

        $this->assertCount(3, $book->reviews);
        $this->assertTrue(
            $book->reviews->contains($reviews->first())
        );
    }

    /** @test */
    public function 書籍がお気に入り登録される(): void
    {
        $book = Book::factory()->create();
        $favorite = Favorite::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertCount(1, $book->favorites);
        $this->assertTrue(
            $book->favorites->contains($favorite)
        );
    }
}
