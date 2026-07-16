<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;

class FavoriteController extends Controller
{
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
}
