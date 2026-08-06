<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポート画面を表示する
     *
     * @return View マイ読書レポート画面
     */
    public function index(): View
    {
        $user = auth()->user();

        $userReviews = $user
            ->reviews()
            ->with('book.genres')
            ->get();

        $ratingCounts = $userReviews
            ->groupBy('rating')
            ->map->count();

        $ratingDistribution = collect(range(1, 5))
            ->map(function ($rating) use ($ratingCounts) {
                return $ratingCounts->get($rating, 0);
            });

        $topRatedBooks = $userReviews
            ->where('rating', '>=', 4)
            ->sortBy([
                ['rating', 'desc'],
                ['created_at', 'desc'],
                ['id', 'asc'],
            ])
            ->take(5)
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->values();

        $genreReviews = $userReviews->flatMap(function ($review) {
            return $review->book->genres->map(function ($genre) use ($review) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'rating' => $review->rating,
                ];
            });
        });

        $genreRatings = $genreReviews
            ->groupBy('id')
            ->map(function ($reviews, $genreId) {
                return [
                    'id' => $genreId,
                    'name' => $reviews->first()['name'],
                    'count' => $reviews->count(),
                    'average_rating' => $reviews->avg('rating'),
                ];
            })
            ->sortBy([
                ['average_rating', 'desc'],
                ['count', 'desc'],
                ['name', 'asc'],
            ])
            ->take(5)
            ->values();

        $stats = [
            'summary' => [
                'total_reviews' => $userReviews->count(),

                'books_read' => $user
                    ->readingPlans()
                    ->where('status', ReadingPlanStatus::Completed)
                    ->count(),

                'average_rating' => $userReviews->avg('rating'),
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
