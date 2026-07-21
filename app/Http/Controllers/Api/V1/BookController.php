<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Resources\BookDetailResource;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
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
                $query->where('genre_id', $genreId);
            });
        }

        $perPage = $validated['per_page'] ?? 10;

        $books = $query->latest()->paginate($perPage);

        return BookResource::collection($books);
    }

    public function show(Book $book): BookDetailResource
    {
        $book->load(
            'genres',
            'reviews.user',
        );

        return new BookDetailResource($book);
    }
}
