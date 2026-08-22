<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーはジャンル一覧を取得できる(): void
    {
        $user = User::factory()->create();
        Genre::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertOk();
        $response->assertViewIs('genres.index');

        $response->assertViewHas('genres');
    }

    /** @test */
    public function ジャンル一覧を名前順で表示できる(): void
    {
        $user = User::factory()->create();

        $thirdGenre = Genre::factory()->create([
            'name' => 'さしすせそ',
        ]);

        $firstGenre = Genre::factory()->create([
            'name' => 'あいうえお',
        ]);

        $secondGenre = Genre::factory()->create([
            'name' => 'かきくけこ',
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.index'));

        $response->assertOk();

        $response->assertViewHas('genres', function ($genres) use ($firstGenre, $secondGenre, $thirdGenre) {
            return $genres->pluck('id')->all() === [
                $firstGenre->id,
                $secondGenre->id,
                $thirdGenre->id,
            ];
        });
    }

    /** @test */
    public function 認証済みユーザーはジャンル詳細画面を表示できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');

        $response->assertViewHas('genre', $genre);
        $response->assertViewHas('books');
    }

    /** @test */
    public function ジャンル詳細画面の書籍一覧をタイトル順で表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $thirdBook = Book::factory()->create([
            'title' => 'さしすせそ',
        ]);

        $firstBook = Book::factory()->create([
            'title' => 'あいうえお',
        ]);

        $secondBook = Book::factory()->create([
            'title' => 'かきくけこ',
        ]);

        $genre->books()->attach([
            $thirdBook->id,
            $firstBook->id,
            $secondBook->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertOk();

        $response->assertViewHas('books', function ($books) use ($firstBook, $secondBook, $thirdBook) {
            return $books->pluck('id')->all() === [
                $firstBook->id,
                $secondBook->id,
                $thirdBook->id,
            ];
        });
    }

    /** @test */
    public function ジャンル詳細画面で紐付く書籍はページネーションされている(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();
        $books = Book::factory()->count(15)->create();

        foreach ($books as $book) {
            $genre->books()->attach($book->id);
        }

        $response = $this->actingAs($user)
            ->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.show');

        $response->assertViewHas('books', function ($books) {
            return $books->count() === 10
                && $books->total() === 15;
        });
    }

    /** @test */
    public function 存在しないジャンルidで詳細画面にアクセスすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.show', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーはジャンル登録画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.create'));

        $response->assertOk();
        $response->assertViewIs('genres.create');
    }

    /** @test */
    public function 認証済みユーザーはジャンルを登録できる(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'テストジャンル',
        ];

        $response = $this->actingAs($user)
            ->post(route('genres.store'), $data);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました。');

        $this->assertDatabaseHas('genres', [
            'name' => $data['name'],
        ]);
    }

    /** @test */
    public function ジャンル登録時にジャンル名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル登録時にジャンル名は1文字でも登録できる(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'あ',
        ];

        $response = $this->actingAs($user)
            ->post(route('genres.store'), $data);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました。');

        $this->assertDatabaseHas('genres', [
            'name' => $data['name'],
        ]);
    }

    /** @test */
    public function ジャンル登録時にジャンル名は255文字まで登録できる(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => str_repeat('あ', 255),
        ];

        $response = $this->actingAs($user)
            ->post(route('genres.store'), $data);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを登録しました。');

        $this->assertDatabaseHas('genres', [
            'name' => $data['name'],
        ]);
    }

    /** @test */
    public function ジャンル登録時にジャンル名が256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => str_repeat('あ', 256),
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ジャンル登録時にすでに登録されているジャンル名だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $existingGenre = Genre::factory()->create([
            'name' => '登録済みジャンル名',
        ]);

        $response = $this->actingAs($user)
            ->post(route('genres.store'), [
                'name' => $existingGenre->name,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function 認証済みユーザーはジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.edit', $genre));

        $response->assertOk();
        $response->assertViewIs('genres.edit');

        $response->assertViewHas('genre', $genre);
    }

    /** @test */
    public function 存在しないジャンルidで編集画面にアクセスすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('genres.edit', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 認証済みユーザーはジャンル名を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '更新前のジャンル名',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '更新後のジャンル名',
            ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました。');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新後のジャンル名',
        ]);
    }

    /** @test */
    public function 存在しないジャンルidで更新しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('genres.update', 999999), [
                'name' => '更新後のジャンル名',
            ]);

        $response->assertNotFound();
    }

    /** @test */
    public function ジャンル更新時にジャンル名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => '更新前のジャンル名',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新前のジャンル名',
        ]);
    }

    /** @test */
    public function ジャンル更新時に自分自身のジャンル名だと重複エラーにならない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create([
            'name' => 'テストジャンル名',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => $genre->name,
            ]);

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを更新しました。');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    /** @test */
    public function ジャンル更新時に他のジャンルと同じ名前だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create([
            'name' => '更新前のジャンル名',
        ]);

        $existingGenre = Genre::factory()->create([
            'name' => '既存のジャンル名',
        ]);

        $response = $this->actingAs($user)
            ->put(route('genres.update', $genre), [
                'name' => $existingGenre->name,
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新前のジャンル名',
        ]);
    }

    /** @test */
    public function 認証済みユーザーはジャンルを削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('success', 'ジャンルを削除しました。');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /** @test */
    public function 存在しないジャンルidで削除しようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', 999999));

        $response->assertNotFound();
    }

    /** @test */
    public function 紐付く書籍があるジャンルは削除できない(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        $genre->books()->attach($book->id);

        $response = $this->actingAs($user)
            ->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $response->assertSessionHas('error', 'このジャンルには書籍が登録されているため、削除できません。');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);

        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }
}
