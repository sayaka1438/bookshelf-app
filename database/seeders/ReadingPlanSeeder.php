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
            // 山田太郎：進行中・期限内
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784101010014'],
                'target_date' => $today->copy()->addDays(7),
                'status' => ReadingPlanStatus::InProgress,
                'completed_at' => null,
            ],
            // 山田太郎：読了済み
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784422100524'],
                'target_date' => $today->copy()->subDays(3),
                'status' => ReadingPlanStatus::Completed,
                'completed_at' => $today->copy()->subDays(2),
            ],
            // 山田太郎：期限超過済み
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784863940246'],
                'target_date' => $today->copy()->subDays(8),
                'status' => ReadingPlanStatus::Overdue,
                'completed_at' => null,
            ],
            // 日次バッチで期限超過へ更新される対象
            [
                'user_id' => $users['yamada@example.com'],
                'book_id' => $books['9784873115658'],
                'target_date' => $today->copy()->subDay(),
                'status' => ReadingPlanStatus::InProgress,
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
        ];

        foreach ($readingPlans as $readingPlan) {
            ReadingPlan::create($readingPlan);
        }
    }
}
