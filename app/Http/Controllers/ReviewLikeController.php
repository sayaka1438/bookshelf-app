<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    /**
     * レビューのいいねを追加または解除する
     *
     * @param  Review  $review  対象のレビュー
     * @return RedirectResponse 元の画面へリダイレクト
     */
    public function toggle(Review $review): RedirectResponse
    {
        $user = auth()->user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            $user->likedReviews()->detach($review->id);

            return back()->with('success', 'いいねを解除しました。');
        }

        $user->likedReviews()->attach($review->id);

        return back()->with('success', 'いいねしました。');
    }
}
