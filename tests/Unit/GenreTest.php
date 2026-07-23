<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ジャンルに設定したfillableを確認できる(): void
    {
        $genre = new Genre;

        $this->assertEquals([
            'name',
        ], $genre->getFillable());
    }

    /** @test */
    public function ジャンルに複数の書籍を紐付けられる(): void
    {
        $genre = Genre::factory()->create();
        $books = Book::factory()->count(3)->create();

        $genre->books()->sync($books->pluck('id'));

        $this->assertCount(3, $genre->books);
        $this->assertTrue(
            $genre->books->contains($books->first())
        );
    }
}
