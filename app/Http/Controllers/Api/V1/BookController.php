<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得するAPI
     *
     * @param  IndexBookRequest  $request  書籍一覧検索用の入力値
     * @return AnonymousResourceCollection 書籍一覧
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $keyword = $validated['keyword'] ?? null;

        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        $genreId = $validated['genre_id'] ?? null;
        if ($genreId) {
            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->whereKey($genreId);
            });
        }

        $perPage = $validated['per_page'] ?? 10;

        $books = $query->latest()->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 書籍詳細を取得するAPI
     *
     * @param  Book  $book  対象の書籍
     * @return BookDetailResource 書籍詳細
     */
    public function show(Book $book): BookDetailResource
    {
        $book->load(
            'genres',
            'reviews.user',
        );

        return new BookDetailResource($book);
    }

    /**
     * 書籍を登録し、選択されたジャンルを紐付けるAPI
     *
     * @param  StoreBookRequest  $request  書籍登録用の入力値
     * @return JsonResponse 登録した書籍情報
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = DB::transaction(function () use ($validated, $genres) {
            $book = Book::create([
                'user_id' => auth()->id(),
                ...$validated,
            ]);

            $book->genres()->sync($genres);

            return $book;
        });

        $book->load('genres');

        return (new BookDetailResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 書籍を更新し、選択されたジャンルを紐付けるAPI
     *
     * @param  UpdateBookRequest  $request  書籍更新用の入力値
     * @param  Book  $book  更新対象の書籍
     * @return BookDetailResource 更新した書籍情報
     */
    public function update(UpdateBookRequest $request, Book $book): BookDetailResource
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genres) {
            $book->update($validated);

            $book->genres()->sync($genres);
        });

        $book->load('genres');

        return new BookDetailResource($book);
    }

    /**
     * 書籍を削除するAPI
     *
     * @param  Book  $book  削除対象の書籍
     * @return Response 204 No Content
     */
    public function destroy(Book $book): Response
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}
