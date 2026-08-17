<?php

namespace Tests\Unit;

use App\Enums\ReadingPlanStatus;
use PHPUnit\Framework\TestCase;

class ReadingPlanStatusTest extends TestCase
{
    /** @test */
    public function 各ステータスに対応するラベルを取得できる(): void
    {
        $this->assertSame(
            '進行中',
            ReadingPlanStatus::InProgress->label()
        );

        $this->assertSame(
            '完了',
            ReadingPlanStatus::Completed->label()
        );

        $this->assertSame(
            '期限切れ',
            ReadingPlanStatus::Expired->label()
        );
    }

    /** @test */
    public function 各ステータスに対応するバッジクラスを取得できる(): void
    {
        $this->assertSame(
            'bg-blue-100 text-blue-800',
            ReadingPlanStatus::InProgress->badgeClass()
        );

        $this->assertSame(
            'bg-green-100 text-green-800',
            ReadingPlanStatus::Completed->badgeClass()
        );

        $this->assertSame(
            'bg-red-100 text-red-800',
            ReadingPlanStatus::Expired->badgeClass()
        );
    }
}
