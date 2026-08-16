<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProcessReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 期限切れの読書計画を失効状態に更新できる(): void
    {
        $user = User::factory()->create();

        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->subDay()->toDateString(),
        ]);

        $activePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->toDateString(),
        ]);

        $this->artisan('app:process-reading-plans')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $activePlan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    /** @test */
    public function 期日3日前の読書計画にリマインダー通知を送信できる(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->addDays(3)->toDateString(),
        ]);

        $this->artisan('app:process-reading-plans')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    /** @test */
    public function 期日当日の読書計画にリマインダー通知を送信できる(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->toDateString(),
        ]);

        $this->artisan('app:process-reading-plans')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    /** @test */
    public function 期日3日後の読書計画にリマインダー通知を送信できる(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->subDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Expired,
        ]);

        $this->artisan('app:process-reading-plans')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    /** @test */
    public function 対象外の日付の読書計画にはリマインダー通知を送信しない(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => today()->addDay()->toDateString(),
        ]);

        $this->artisan('app:process-reading-plans')
            ->assertExitCode(0);

        Notification::assertNotSentTo(
            $user,
            ReadingPlanReminderNotification::class
        );
    }

    /** @test */
    public function 読書計画処理コマンドが毎日20時にスケジュール登録されている(): void
    {
        $schedule = app(Schedule::class);

        $events = $schedule->events();

        $event = collect($events)->first(function ($event) {
            return str_contains(
                $event->command,
                'app:process-reading-plans'
            );
        });

        $this->assertNotNull($event);

        $this->assertSame('0 20 * * *', $event->expression);
    }
}
