<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewLikeController extends Controller
{
    // レビューいいねトグル
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
