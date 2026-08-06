<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧を表示する
     *
     * @return View 読書計画一覧画面
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->query('status');

        $readingPlans = auth()->user()
            ->readingPlans()
            ->with('book')
            ->filterByStatus($currentStatus)
            ->get();

        return view('reading-plans.index', compact(
            'readingPlans',
            'currentStatus',
        ));
    }

    /**
     * 読書計画登録画面を表示する
     *
     * @return View 読書計画登録画面
     */
    public function create(): View
    {
        $registeredBookIds = auth()->user()
            ->readingPlans()
            ->pluck('book_id');

        $books = Book::whereNotIn('id', $registeredBookIds)
            ->orderBy('title')
            ->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録する
     *
     * @param  StoreReadingPlanRequest  $request  読書計画登録用の入力値
     * @return RedirectResponse 読書計画一覧画面へリダイレクト
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => auth()->id(),
            'status' => ReadingPlanStatus::InProgress,
            ...$validated,
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を作成しました。');
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
