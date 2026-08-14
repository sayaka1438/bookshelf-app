<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function isbnから書籍情報を取得できる(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'リーダブルコード',
                            'authors' => [
                                'Dustin Boswell',
                            ],
                            'publishedDate' => '2012-06-23',
                            'description' => '説明文',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.isbn.search', [
                'isbn' => '9784101010014',
            ]));

        $response->assertOk();

        $response->assertJson([
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell',
            'published_date' => '2012-06-23',
            'description' => '説明文',
            'image_url' => 'https://example.com/image.jpg',
        ]);
    }

    /** @test */
    public function 出版日が年月日形式でない場合はnullを返す(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'リーダブルコード',
                            'authors' => [
                                'Dustin Boswell',
                            ],
                            'publishedDate' => '2012',
                            'description' => '説明文',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/image.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.isbn.search', [
                'isbn' => '9784101010014',
            ]));

        $response->assertOk();

        $response->assertJson([
            'published_date' => null,
        ]);
    }

    /** @test */
    public function isbnが13桁以外だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('books.isbn.search', [
                'isbn' => '123',
            ]));

        $response->assertStatus(422);

        $response->assertJson([
            'error' => 'ISBNは13桁で入力してください。',
        ]);
    }

    /** @test */
    public function google_books_apiの取得に失敗すると502エラーになる(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([], 500),
        ]);

        $response = $this->actingAs($user)->get(
            route('books.isbn.search', [
                'isbn' => '9784101010014',
            ])
        );

        $response->assertStatus(502);

        $response->assertJson([
            'error' => '書籍情報の取得に失敗しました。',
        ]);
    }

    /** @test */
    public function 書籍が見つからない場合は404エラーになる(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->get(route('books.isbn.search', [
                'isbn' => '9784101010014',
            ]));

        $response->assertStatus(404);

        $response->assertJson([
            'error' => '書籍が見つかりませんでした。',
        ]);
    }
}
