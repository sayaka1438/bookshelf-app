<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーは書籍一覧を取得できる(): void
    {
        Book::factory()->create();

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

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();

        $response->assertSee($titleMatchedBook->title);
        $response->assertSee($authorMatchedBook->title);

        $response->assertDontSee($unmatchedBook->title);
    }

    /** @test */
    public function キーワードが256文字以上だとバリデーションエラーになる(): void
    {
        $response = $this->get(route('books.index', [
            'keyword' => str_repeat('あ', 256),
        ]));

        $response->assertSessionHasErrors('keyword');
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

        $response = $this->get(route('books.index', [
            'genre' => $targetGenre->id,
        ]));

        $response->assertOk();

        $response->assertSee($matchedBook1->title);
        $response->assertSee($matchedBook2->title);

        $response->assertDontSee($unmatchedBook->title);
    }

    /** @test */
    public function 存在しないジャンルだとバリデーションエラーになる(): void
    {
        $response = $this->get(route('books.index', [
            'genre' => 999999,
        ]));

        $response->assertSessionHasErrors('genre');
    }

    /** @test */
    public function newestで書籍を並び替えできる(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $middleBook = Book::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'newest',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            $newBook->title,
            $middleBook->title,
            $oldBook->title,
        ]);
    }

    /** @test */
    public function oldestで書籍を並び替えできる(): void
    {
        $oldBook = Book::factory()->create([
            'created_at' => now()->subDays(2),
        ]);

        $middleBook = Book::factory()->create([
            'created_at' => now()->subDay(),
        ]);

        $newBook = Book::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'oldest',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            $oldBook->title,
            $middleBook->title,
            $newBook->title,
        ]);
    }

    /** @test */
    public function titleで書籍を並び替えできる(): void
    {
        $firstBook = Book::factory()->create([
            'title' => 'あいうえお',
        ]);

        $secondBook = Book::factory()->create([
            'title' => 'かきくけこ',
        ]);

        $thirdBook = Book::factory()->create([
            'title' => 'さしすせそ',
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'title',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            $firstBook->title,
            $secondBook->title,
            $thirdBook->title,
        ]);
    }

    /** @test */
    public function ratingで書籍を並び替えできる(): void
    {
        $highRatedBook = Book::factory()->create();
        $middleRatedBook = Book::factory()->create();
        $lowRatedBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $highRatedBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $middleRatedBook->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'book_id' => $lowRatedBook->id,
            'rating' => 1,
        ]);

        $response = $this->get(route('books.index', [
            'sort' => 'rating',
        ]));

        $response->assertOk();

        $response->assertSeeInOrder([
            $highRatedBook->title,
            $middleRatedBook->title,
            $lowRatedBook->title,
        ]);
    }

    /** @test */
    public function 検索条件を維持したままページネーションできる(): void
    {
        Book::factory()->count(15)->create([
            'title' => 'Laravel',
        ]);

        Book::factory()->count(5)->create([
            'title' => 'PHP',
        ]);

        $response = $this->get(route('books.index', [
            'keyword' => 'Laravel',
        ]));

        $response->assertOk();

        $response->assertViewHas(
            'books',
            function ($books) {
                return $books->count() === 10
                    && $books->total() === 15;
            }
        );

        $response->assertSee('keyword=Laravel&amp;page=2', false);
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
    public function 存在しない書籍idで詳細画面にアクセスすると404エラーになる(): void
    {
        $response = $this->get(route('books.show', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーは書籍登録画面を表示できる(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create();

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
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
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

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => '',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function 書籍登録時にタイトルは1文字でも登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'あ',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
        ]);
    }

    /** @test */
    public function 書籍登録時にタイトルは255文字まで登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => str_repeat('あ', 255),
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
        ]);
    }

    /** @test */
    public function 書籍登録時にタイトルが256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => str_repeat('あ', 256),
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function 書籍登録時に著者名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function 書籍登録時に著者名は1文字でも登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => 'あ',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
        ]);
    }

    /** @test */
    public function 書籍登録時に著者名は255文字まで登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => str_repeat('あ', 255),
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
        ]);
    }

    /** @test */
    public function 書籍登録時に著者名が256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => str_repeat('あ', 256),
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('author');
    }

    /** @test */
    public function 書籍登録時にisbnと出版日が空でも登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '',
            'published_date' => '',
            'genres' => [$genre->id],
        ];

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('title', $data['title'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => null,
            'published_date' => null,
        ]);
    }

    /** @test */
    public function 書籍登録時にisbnが12桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '123456789012',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時にisbnが14桁だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '12345678901234',
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時にすでに登録されているisbnだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $existingBook = Book::factory()->create([
            'isbn' => '1234567890123',
        ]);

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => $existingBook->isbn,
                'published_date' => '2026-07-23',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('isbn');
    }

    /** @test */
    public function 書籍登録時に出版日が不正な値だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '不正な値',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('published_date');
    }

    /** @test */
    public function 書籍登録時に出版日が存在しない日付だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-02-30',
                'genres' => [$genre->id],
            ]);

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

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
            'description' => $data['description'],
        ]);
    }

    /** @test */
    public function 書籍登録時に説明が1001文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'description' => str_repeat('あ', 1001),
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('description');
    }

    /** @test */
    public function 書籍登録時に画像urlが不正な形式だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'image_url' => '不正な形式',
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('image_url');
    }

    /** @test */
    public function 書籍登録時に画像urlは255文字まで登録できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $prefix = 'https://example.com/';
        $imageUrl = $prefix.str_repeat('a', 255 - strlen($prefix));

        $data = [
            'title' => 'テストタイトル',
            'author' => '著者名',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-23',
            'image_url' => $imageUrl,
            'genres' => [$genre->id],
        ];

        $this->assertSame(255, strlen($imageUrl));

        $response = $this->actingAs($user)
            ->post(route('books.store'), $data);

        $book = Book::where('isbn', $data['isbn'])->firstOrFail();

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success', '書籍を登録しました。');

        $this->assertDatabaseHas('books', [
            'user_id' => $user->id,
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'published_date' => $data['published_date'],
            'image_url' => $data['image_url'],
        ]);
    }

    /** @test */
    public function 書籍登録時に画像urlが256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $prefix = 'https://example.com/';
        $imageUrl = $prefix.str_repeat('a', 256 - strlen($prefix));

        $this->assertSame(256, strlen($imageUrl));

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'image_url' => $imageUrl,
                'genres' => [$genre->id],
            ]);

        $response->assertSessionHasErrors('image_url');

    }

    /** @test */
    public function 書籍登録時にジャンルが未選択だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [],
            ]);

        $response->assertSessionHasErrors('genres');
    }

    /** @test */
    public function 書籍登録時にジャンルidが文字列だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => ['文字列'],
            ]);

        $response->assertSessionHasErrors('genres.0');
    }

    /** @test */
    public function 書籍登録時に存在しないジャンルだとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => '著者名',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-23',
                'genres' => [999999],
            ]);

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
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.edit', $book));

        $response->assertForbidden();
    }

    /** @test */
    public function 存在しない書籍idで編集画面にアクセスすると404エラーになる(): void
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
            'title' => '更新前のタイトル',
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
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
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
                'author' => $book->author,
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
    public function 書籍更新時に自分自身のisbnは重複エラーにならない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $user->id,
            'title' => '更新前のタイトル',
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
    public function 書籍更新時に他の書籍と同じisbnだとバリデーションエラーになる(): void
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
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('books.destroy', $book));

        $response->assertForbidden();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
