<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


class BookController extends Controller
{
    //　書籍一覧
    public function index(): View
    {
        $books = Book::with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    //　書籍登録画面表示
    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    //　書籍登録
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        $book = Book::create([
            ...$validated,
            'user_id' => auth()->id(),
        ]);

        $book->genres()->sync($genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    //　書籍詳細
    public function show(Book $book): View
    {
        $book->load(
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        );

        return view('books.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
