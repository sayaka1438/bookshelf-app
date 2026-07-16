<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    // お気に入りトグル
    public function toggle(Book $book): RedirectResponse
    {
        $user = auth()->user();

        if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
            $user->favoriteBooks()->detach($book->id);

            return back()->with('success', 'お気に入りを解除しました。');
        }

        $user->favoriteBooks()->attach($book->id);

        return back()->with('success', 'お気に入りに追加しました。');
    }

    // お気に入り一覧
    public function index(): View
    {
        $books = auth()->user()
            ->favoriteBooks()
            ->latest()
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }
}
