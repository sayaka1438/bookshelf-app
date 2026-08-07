<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
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
     * 読書計画編集画面を表示する
     *
     * @param  ReadingPlan  $plan  編集対象の読書計画
     * @return View 読書計画編集画面
     */
    public function edit(ReadingPlan $plan): View
    {
        $this->authorize('update', $plan);

        $plan->load('book');

        return view('reading-plans.edit', [
            'readingPlan' => $plan,
        ]);
    }

    /**
     * 読書計画を更新する
     *
     * @param  UpdateReadingPlanRequest  $request  読書計画更新用の入力値
     * @param  ReadingPlan  $plan  更新対象の読書計画
     * @return RedirectResponse 読書計画一覧画面へリダイレクト
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        $plan->update($request->validated());

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除する
     *
     * @param  ReadingPlan  $plan  削除対象の読書計画
     * @return RedirectResponse 読書計画一覧画面へリダイレクト
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画を完了状態に更新する
     *
     * @param  ReadingPlan  $plan  完了対象の読書計画
     * @return RedirectResponse 読書計画一覧画面へリダイレクト
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('complete', $plan);

        $plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')
            ->with('success', '読書計画を完了しました。');
    }
}
