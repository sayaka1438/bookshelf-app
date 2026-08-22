<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * レビューを登録する
     *
     * @param  StoreReviewRequest  $request  レビュー登録用の入力値
     * @param  Book  $book  対象の書籍
     * @return RedirectResponse 対象書籍の詳細画面へリダイレクト
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();

        Review::create([
            ...$validated,
            'user_id' => auth()->id(),
            'book_id' => $book->id,
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを投稿しました。');
    }

    /**
     * レビュー編集画面を表示する
     *
     * @param  Review  $review  対象のレビュー
     * @return View レビュー編集画面
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        $review->load('book');

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     *
     * @param  UpdateReviewRequest  $request  レビュー更新用の入力値
     * @param  Review  $review  対象のレビュー
     * @return RedirectResponse 対象書籍の詳細画面へリダイレクト
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update($request->validated());

        return redirect()->route('books.show', $review->book)
            ->with('success', 'レビューを更新しました。');
    }

    /**
     * レビューを削除する
     *
     * @param  Review  $review  対象のレビュー
     * @return RedirectResponse 対象書籍の詳細画面へリダイレクト
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);

        $book = $review->book;

        $review->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'レビューを削除しました。');
    }
}
