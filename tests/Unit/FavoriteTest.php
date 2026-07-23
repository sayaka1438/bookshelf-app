<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お気に入りに設定したfillableを確認できる(): void
    {
        $favorite = new Favorite;

        $this->assertEquals([
            'user_id',
            'book_id',
        ], $favorite->getFillable());
    }

    /** @test */
    public function お気に入りが特定のユーザーに属している(): void
    {
        $user = User::factory()->create();
        $favorite = Favorite::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($favorite->user->is($user));
    }

    /** @test */
    public function お気に入りが特定の書籍に属している(): void
    {
        $book = Book::factory()->create();
        $favorite = Favorite::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertTrue($favorite->book->is($book));
    }
}
