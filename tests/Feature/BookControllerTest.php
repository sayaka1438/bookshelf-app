<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーは書籍一覧を取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.index'));

        $response->assertOk();
        $response->assertViewIs('books.index');
        $response->assertViewHas('books');
    }

    /** @test */
    public function 認証済みユーザーは書籍一覧を取得できる(): void
    {
        $user = User::factory()->create();
        Book::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.index'));

        $response->assertOk();
    }

    /** @test */
    public function 書籍一覧は10件ずつページネーションされる(): void
    {
        Book::factory()->count(15)->create();

        $response = $this->get(route('books.index'));

        $response->assertOk();

        $response->assertViewHas(
            'books',
            function ($books) {
                return $books->count() === 10
                    && $books->total() === 15;
            }
        );
    }

    /** @test */
    public function 未認証ユーザーは書籍詳細を取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->get(route('books.show', $book));

        $response->assertOk();
        $response->assertViewIs('books.show');
        $response->assertViewHas('book', $book);
    }

    /** @test */
    public function 認証済みユーザーは書籍詳細を取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.show', $book));

        $response->assertOk();
    }

    /** @test */
    public function 存在しない書籍_i_dで詳細画面にアクセスすると404エラーになる(): void
    {
        $response = $this->get(route('books.show', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは書籍登録画面を表示できる(): void
    {
        $user = User::factory()->create();
        Genre::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->get(route('books.create'));

        $response->assertOk();
        $response->assertViewIs('books.create');

        $response->assertViewHas('genres');
    }

    /** @test */
    public function 認証済みユーザーは書籍を登録できる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'user_id' => $user->id,
        ]);

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    /** @test */
    public function 書籍登録時にタイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => '',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function 書籍登録時に著者名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function 書籍登録時に_isb_nが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時に出版日が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('published_date');
    }

    /** @test */
    public function 書籍登録時にジャンルが未選択だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('genres');
    }

    /** @test */
    public function 書籍登録時に_isb_nが14桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '12345678901234',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時にすでに登録されている_isb_nだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $existingBook = Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => $existingBook->isbn,
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時に出版日が不正な値だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '不正な値',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('published_date');
    }

    /** @test */
    public function 書籍登録時に説明は1000文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'description' => str_repeat('あ', 1000),
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $this->assertDatabaseHas('books', [
            'isbn' => $data['isbn'],
        ]);
    }

    /** @test */
    public function 書籍登録時に説明は1001文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'description' => str_repeat('あ', 1001),
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function 書籍登録時に画像_ur_lが不正な形式だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'image_url' => '不正な形式',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('image_url');
    }

    /** @test */
    public function 書籍登録時に存在しないジャンルだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [999999],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $response->assertSessionHasErrors('genres.0');
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した書籍の編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->sync([$genre->id]);

        $response = $this->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertOk();
        $response->assertViewIs('books.edit');

        $response->assertViewHas('book', $book);
        $response->assertViewHas('genres');
    }

    /** @test */
    public function 他人が登録した書籍の編集画面にアクセスすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    /** @test */
    public function 存在しない書籍_i_dで編集画面にアクセスすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.edit', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは自分の登録した書籍を編集できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '更新後のタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました。');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /** @test */
    public function 他人が登録した書籍を編集しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'title' => '更新前のタイトル',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->put(route('books.update', $book), [
                'title' => '更新後のタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'genres' => [$genre->id],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新前のタイトル',
        ]);
    }

    /** @test */
    public function 書籍更新時にタイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '',
                'author' => '更新後の著者名',
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => $book->title,
        ]);
    }

    /** @test */
    public function 書籍更新時に自分自身の_isb_nは重複エラーにならない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => '更新後のタイトル',
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'genres' => [$genre->id],
            ]);

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を更新しました。');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
            'isbn' => $book->isbn,
        ]);
    }

    /** @test */
    public function 書籍更新時に他の書籍と同じ_isb_nだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'isbn' => '1111111111111',
            'user_id' => $user->id,
        ]);

        $existingBook = Book::factory()->create([
            'isbn' => '2222222222222',
        ]);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $existingBook->isbn,
                'published_date' => $book->published_date,
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'isbn' => '1111111111111',
        ]);
    }

    /** @test */
    public function 書籍更新時にジャンルの紐付けも更新される(): void
    {
        $user = User::factory()->create();

        $oldGenre = Genre::factory()->create();
        $newGenre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->attach($oldGenre->id);

        $response = $this->actingAs($user)
            ->put(route('books.update', $book), [
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'published_date' => $book->published_date,
                'genres' => [$newGenre->id],
            ]);

        $response->assertRedirect(route('books.show', $book));

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
    public function 認証済みユーザーは自分の登録した書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', '書籍を削除しました。');

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    /** @test */
    public function 他人が登録した書籍を削除しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'title' => '削除前のタイトル',
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '削除前のタイトル',
        ]);
    }
}
