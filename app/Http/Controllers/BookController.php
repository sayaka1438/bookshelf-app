<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を検索条件に応じて表示する
     *
     * @param  IndexBookRequest  $request  書籍一覧検索用の入力値
     * @return View 書籍一覧画面
     */
    public function index(IndexBookRequest $request): View
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

        $genreId = $validated['genre'] ?? null;

        if ($genreId) {
            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->whereKey($genreId);
            });
        }

        $sort = $validated['sort'] ?? 'newest';

        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('reviews_count');
                break;

            case 'title':
                $query->orderBy('title', 'asc');
                break;

            case 'newest':
            default:
                $query->latest();
                break;
        }

        $books = $query->paginate(10)->withQueryString();

        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍登録画面を表示する
     *
     * @return View 書籍登録画面
     */
    public function create(): View
    {
        $genres = Genre::orderBy('name')->get();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を登録し、選択されたジャンルを紐付ける
     *
     * @param  StoreBookRequest  $request  書籍登録用の入力値
     * @return RedirectResponse 登録した書籍の詳細画面へリダイレクト
     */
    public function store(StoreBookRequest $request): RedirectResponse
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

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    /**
     * 書籍詳細を表示する
     *
     * @param  Book  $book  表示対象の書籍
     * @return View 書籍詳細画面
     */
    public function show(Book $book): View
    {
        $book->load([
            'genres',
            'reviews' => function ($query) {
                $query->latest('created_at')
                    ->with('user', 'likedByUsers');
            },
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集画面を表示する
     *
     * @param  Book  $book  編集対象の書籍
     * @return View 書籍編集画面
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $book->load('genres');
        $genres = Genre::orderBy('name')->get();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍を更新し、選択されたジャンルを紐付ける
     *
     * @param  UpdateBookRequest  $request  書籍更新用の入力値
     * @param  Book  $book  更新対象の書籍
     * @return RedirectResponse 更新した書籍の詳細画面へリダイレクト
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $genres = $validated['genres'];
        unset($validated['genres']);

        DB::transaction(function () use ($book, $validated, $genres) {
            $book->update($validated);

            $book->genres()->sync($genres);
        });

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍を削除する
     *
     * @param  Book  $book  削除対象の書籍
     * @return RedirectResponse 書籍一覧画面へリダイレクト
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
