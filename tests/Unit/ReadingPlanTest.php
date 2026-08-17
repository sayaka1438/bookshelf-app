<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 読書計画に設定したfillableを確認できる(): void
    {
        $readingPlan = new ReadingPlan;

        $this->assertEquals([
            'user_id',
            'book_id',
            'target_date',
            'status',
            'completed_at',
        ], $readingPlan->getFillable());
    }

    /** @test */
    public function 読書計画が特定のユーザーに属している(): void
    {
        $user = User::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertTrue($readingPlan->user->is($user));
    }

    /** @test */
    public function 読書計画が特定の書籍に属している(): void
    {
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertTrue($readingPlan->book->is($book));
    }

    /** @test */
    public function ステータスが列挙型としてキャストされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $this->assertSame(
            ReadingPlanStatus::InProgress,
            $readingPlan->status
        );
    }

    /** @test */
    public function 期日が日付としてキャストされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'target_date' => '2026-08-17',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $readingPlan->target_date
        );

        $this->assertSame(
            '2026-08-17',
            $readingPlan->target_date->toDateString()
        );
    }

    /** @test */
    public function 完了日が日時としてキャストされる(): void
    {
        $readingPlan = ReadingPlan::factory()->create([
            'completed_at' => '2026-08-17 00:00:00',
        ]);

        $this->assertInstanceOf(
            Carbon::class,
            $readingPlan->completed_at
        );

        $this->assertSame(
            '2026-08-17 00:00:00',
            $readingPlan->completed_at->toDateTimeString()
        );
    }

    /** @test */
    public function ステータスを指定すると読書計画を絞り込める(): void
    {
        ReadingPlan::factory()->count(3)->create([
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->count(2)->create([
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $readingPlans = ReadingPlan::filterByStatus(
            ReadingPlanStatus::Completed->value
        )->get();

        $this->assertCount(3, $readingPlans);

        $this->assertTrue(
            $readingPlans->every(
                fn (ReadingPlan $readingPlan) => $readingPlan->status === ReadingPlanStatus::Completed
            )
        );
    }

    /** @test */
    public function ステータスを指定しない場合はすべての読書計画を取得できる(): void
    {
        ReadingPlan::factory()->count(3)->create([
            'status' => ReadingPlanStatus::Completed,
        ]);

        ReadingPlan::factory()->count(2)->create([
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $readingPlans = ReadingPlan::filterByStatus(null)->get();

        $this->assertCount(5, $readingPlans);
    }
}
