<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GoogleBooksController extends Controller
{
    /**
     * ISBNから書籍情報を取得する
     *
     * @param  string  $isbn  ISBN
     * @return JsonResponse 書籍情報
     */
    public function searchByIsbn(string $isbn): JsonResponse
    {
        if (! preg_match('/^\d{13}$/', $isbn)) {
            return response()->json([
                'error' => 'ISBNは13桁で入力してください。',
            ], 422);
        }

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => "isbn:{$isbn}",
            'key' => config('services.google_books.api_key'),
        ]);

        if (! $response->successful()) {
            return response()->json([
                'error' => '書籍情報の取得に失敗しました。',
            ], 502);
        }

        $data = $response->json();

        if (empty($data['items'])) {
            return response()->json([
                'error' => '書籍が見つかりませんでした。',
            ], 404);
        }

        $volumeInfo = $data['items'][0]['volumeInfo'] ?? [];

        $publishedDate = $volumeInfo['publishedDate'] ?? null;

        if ($publishedDate && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $publishedDate)) {
            $publishedDate = null;
        }

        return response()->json([
            'title' => $volumeInfo['title'] ?? null,
            'author' => ! empty($volumeInfo['authors'])
                ? implode(', ', $volumeInfo['authors'])
                : null,
            'published_date' => $publishedDate,
            'description' => $volumeInfo['description'] ?? null,
            'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
        ]);
    }
}
