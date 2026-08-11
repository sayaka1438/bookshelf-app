<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id', 'email');
        $books = Book::pluck('id', 'isbn');

        $today = Carbon::today();

        $readingPlans = [
            // 山田太郎：進行中・通知対象外
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010014'],
                'target_date' => $today->copy()->addDays(7),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 山田太郎：完了済み
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784422100524'],
                'target_date' => $today->copy()->subDays(3),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => $today->copy()->subDays(2),
            ],
            // 山田太郎：期限切れ
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784863940246'],
                'target_date' => $today->copy()->subDays(8),
                'status' => ReadingPlanStatus::Expired,
                'completed_at' => null,
            ],
            // 別ユーザー：認可確認用
            [
                'user_id' => $users['suzuki@example.com'],
                'book_id' => $books['9784101010021'],
                'target_date' => $today->copy()->addDays(10),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 日次バッチで期限切れになる
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784873115658'],
                'target_date' => $today->copy()->subDay(),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // リマインダー通知：期日3日前
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010021'],
                'target_date' => $today->copy()->addDays(3),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // リマインダー通知：期日当日
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784163902302'],
                'target_date' => $today->copy(),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // リマインダー通知：期日3日後
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784822251468'],
                'target_date' => $today->copy()->subDays(3),
                'status' => ReadingPlanStatus::Expired,
                'completed_at' => null,
            ],
        ];

        foreach ($readingPlans as $readingPlan) {
            ReadingPlan::create($readingPlan);
        }
    }
}
