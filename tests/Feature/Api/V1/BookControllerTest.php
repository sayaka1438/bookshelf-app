<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーはページネーションされた書籍一覧を取得できる(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->getJson(route('api.books.index'));

        $response->assertOk();

        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('meta.total', 15);
    }

    /** @test */
    public function 未認証ユーザーは正しいレスポンス構造で書籍一覧を取得できる(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        $response = $this->getJson(route('api.books.index'));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'genres' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'average_rating',
                    'reviews_count',
                ],
            ],
        ]);
    }

    /** @test */
    public function 未認証ユーザーは正しい書籍情報を取得できる(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->getJson(route('api.books.index'));

        $response->assertOk();

        $response->assertJsonFragment([
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'average_rating' => '5.0000',
            'reviews_count' => 1,
        ]);
        $response->assertJsonFragment([
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    /** @test */
    public function キーワードで書籍を検索できる(): void
    {
        $titleMatchedBook = Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '山田太郎',
        ]);

        $authorMatchedBook = Book::factory()->create([
            'title' => 'PHP実践',
            'author' => 'Laravel太郎',
        ]);

        $unmatchedBook = Book::factory()->create([
            'title' => 'SQL入門',
            'author' => '佐藤花子',
        ]);

        $response = $this->getJson(route('api.books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'id' => $titleMatchedBook->id,
        ]);
        $response->assertJsonFragment([
            'id' => $authorMatchedBook->id,
        ]);
        $response->assertJsonMissing([
            'id' => $unmatchedBook->id,
        ]);
    }

    /** @test */
    public function キーワードが256文字以上だとバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('api.books.index', [
            'keyword' => str_repeat('あ', 256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('keyword');
    }

    /** @test */
    public function ジャンルで書籍を絞り込みできる(): void
    {
        $targetGenre = Genre::factory()->create();
        $otherGenre = Genre::factory()->create();

        $matchedBook1 = Book::factory()->create();
        $matchedBook2 = Book::factory()->create();
        $unmatchedBook = Book::factory()->create();

        $matchedBook1->genres()->attach($targetGenre->id);
        $matchedBook2->genres()->attach($targetGenre->id);
        $unmatchedBook->genres()->attach($otherGenre->id);

        $response = $this->getJson(route('api.books.index', [
            'genre_id' => $targetGenre->id,
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'id' => $matchedBook1->id,
        ]);

        $response->assertJsonFragment([
            'id' => $matchedBook2->id,
        ]);

        $response->assertJsonMissing([
            'id' => $unmatchedBook->id,
        ]);
    }

    /** @test */
    public function 存在しないジャンルidだとバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('api.books.index', [
            'genre_id' => 999999,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genre_id');
    }

    /** @test */
    public function 指定したページのデータを取得できる(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->getJson(route('api.books.index', [
            'page' => 2,
        ]));

        $response->assertOk();

        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.last_page', 2);
    }

    /** @test */
    public function pageが0だとバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('api.books.index', [
            'page' => 0,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('page');
    }

    /** @test */
    public function per_pageを指定して書籍一覧を取得できる(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->getJson(route('api.books.index', [
            'per_page' => 5,
        ]));

        $response->assertOk();

        $response->assertJsonCount(5, 'data');
        $response->assertJsonPath('meta.per_page', 5);
    }

    /** @test */
    public function per_pageが0だとバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('api.books.index', [
            'per_page' => 0,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('per_page');
    }

    /** @test */
    public function per_pageが101だとバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('api.books.index', [
            'per_page' => 101,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('per_page');
    }

    /** @test */
    public function 未認証ユーザーは書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();

        $book->genres()->attach($genre->id);

        Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->getJson(route('api.books.show', $book));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'genres' => [
                    '*' => [
                        'id',
                        'name',
                    ],
                ],
                'reviews' => [
                    '*' => [
                        'user' => [
                            'id',
                            'name',
                        ],
                        'rating',
                        'comment',
                        'created_at',
                    ],
                ],
            ],
        ]);
        $response->assertJsonPath('data.id', $book->id);
    }

    /** @test */
    public function 存在しない書籍idだと500エラーになる(): void
    {
        $response = $this->getJson(route('api.books.show', 999999));

        $response->assertStatus(500);
        $response->assertJson([
            'message' => '指定されたデータは存在しません。',
        ]);
    }

    /** @test */
    public function 未認証ユーザーが書籍を登録しようとすると401エラーになる(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->postJson(route('api.books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'genres' => [$genre->id],
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => '認証が必要です。',
        ]);

        $this->assertDatabaseMissing('books', [
            'title' => 'テストタイトル',
        ]);
    }

    /** @test */
    public function 認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson(route('api.books.store'), $data);

        $response->assertCreated();

        $response->assertJsonFragment([
            'isbn' => $data['isbn'],
        ]);

        $response->assertJsonFragment([
            'id' => $genre->id,
            'name' => $genre->name,
        ]);

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'isbn' => $data['isbn'],
        ]);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /** @test */
    public function 書籍登録時にタイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.books.store'), [
            'title' => '',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /** @test */
    public function 書籍登録時にすでに登録されているisbnだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $existingBook = Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

        $response = $this->postJson(route('api.books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => $existingBook->isbn,
            'published_date' => '2026-07-29',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');
    }

    /** @test */
    public function 書籍登録時に説明が1001文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'description' => str_repeat('あ', 1001),
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('description');
    }

    /** @test */
    public function 書籍登録時に画像urlが不正な形式だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'image_url' => '不正な形式',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('image_url');
    }

    /** @test */
    public function 書籍登録時に存在しないジャンルだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(route('api.books.store'), [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-29',
            'genres' => [999999],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('genres.0');
    }

    /** @test */
    public function 未認証ユーザーが書籍を編集しようとすると401エラーになる(): void
    {
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'title' => '更新前のタイトル',
        ]);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => '認証が必要です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した書籍を編集できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'title' => '更新後のタイトル',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $book->user_id,
            'title' => '更新後のタイトル',
        ]);
    }

    /** @test */
    public function 他人が登録した書籍を編集しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '更新前のタイトル',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'この操作を行う権限がありません。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    /** @test */
    public function 書籍更新時にジャンルの紐付けも更新される(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();

        $book->genres()->attach($oldGenre->id);

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$newGenre->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
    }

    /** @test */
    public function 書籍更新時に自分自身のisbnだと重複エラーにならない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    /** @test */
    public function 書籍更新時に他の書籍と同じisbnだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
            'isbn' => '1111111111111',
        ]);

        $existingBook = Book::factory()->create([
            'isbn' => '2222222222222',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(route('api.books.update', $book), [
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $existingBook->isbn,
            'published_date' => $book->published_date,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('isbn');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1111111111111',
        ]);
    }

    /** @test */
    public function 未認証ユーザーが書籍を削除しようとすると401エラーになる(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson(route('api.books.destroy', $book));

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => '認証が必要です。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $book->genres()->attach($genre->id);

        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->deleteJson(route('api.books.destroy', $book));

        $response->assertNoContent();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /** @test */
    public function 他人が登録した書籍を削除しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('api.books.destroy', $book));

        $response->assertForbidden();
        $response->assertJson([
            'message' => 'この操作を行う権限がありません。',
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
