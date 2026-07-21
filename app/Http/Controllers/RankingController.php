<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    // ランキング一覧
    public function index(): View
    {
        $rankedBooks = Book::has('reviews')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->orderByDesc('reviews_count')
            ->orderByDesc('books.created_at')
            ->orderBy('title')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
