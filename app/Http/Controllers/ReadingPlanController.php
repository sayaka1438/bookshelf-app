<?php

namespace App\Http\Controllers;

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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
