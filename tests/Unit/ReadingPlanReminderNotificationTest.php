<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 通知チャネルとしてdatabaseを使用する(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $notification = new ReadingPlanReminderNotification(
            $readingPlan,
            'three_days_before'
        );

        $this->assertSame(
            ['database'],
            $notification->via($user)
        );
    }

    /** @test */
    public function 期日3日前の通知内容を取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'テストタイトル',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $notification = new ReadingPlanReminderNotification(
            $readingPlan,
            'three_days_before'
        );

        $data = $notification->toArray($user);

        $this->assertSame([
            'title' => '読書計画リマインダー',
            'body' => '『テストタイトル』の期日まであと3日です。',
            'timing' => 'three_days_before',
        ], $data);
    }

    /** @test */
    public function 期日当日の通知内容を取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'テストタイトル',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $notification = new ReadingPlanReminderNotification(
            $readingPlan,
            'on_due_date'
        );

        $data = $notification->toArray($user);

        $this->assertSame([
            'title' => '読書計画リマインダー',
            'body' => '『テストタイトル』が本日期日です。',
            'timing' => 'on_due_date',
        ], $data);
    }

    /** @test */
    public function 期日3日後の通知内容を取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'テストタイトル',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $notification = new ReadingPlanReminderNotification(
            $readingPlan,
            'three_days_after'
        );

        $data = $notification->toArray($user);

        $this->assertSame([
            'title' => '読書計画リマインダー',
            'body' => '『テストタイトル』の期日を3日過ぎています。',
            'timing' => 'three_days_after',
        ], $data);
    }

    /** @test */
    public function 未定義のタイミングではデフォルトの通知内容を取得できる(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create([
            'title' => 'テストタイトル',
        ]);

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $notification = new ReadingPlanReminderNotification(
            $readingPlan,
            'unknown'
        );

        $data = $notification->toArray($user);

        $this->assertSame([
            'title' => '読書計画リマインダー',
            'body' => '読書計画の通知です。',
            'timing' => 'unknown',
        ], $data);
    }
}
