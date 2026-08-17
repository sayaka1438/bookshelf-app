<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証済みユーザーは通知一覧を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');

        $response->assertViewHas('notifications');
    }

    /** @test */
    public function 自分の通知だけを表示できる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherReadingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification($readingPlan, 'on_due_date')
        );

        $otherUser->notify(
            new ReadingPlanReminderNotification($otherReadingPlan, 'on_due_date')
        );

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();

        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->count() === 1;
        });
    }

    /** @test */
    public function 認証済みユーザーは自分の通知を既読にできる(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->notify(
            new ReadingPlanReminderNotification($readingPlan, 'on_due_date')
        );

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification));

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success', '通知を既読にしました。');

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    /** @test */
    public function 他人の通知を既読にしようとすると404エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $otherUser->notify(
            new ReadingPlanReminderNotification($readingPlan, 'on_due_date')
        );

        $notification = $otherUser->notifications()->first();

        $response = $this->actingAs($user)
            ->post(route('notifications.read', $notification));

        $response->assertNotFound();

        $notification->refresh();

        $this->assertNull($notification->read_at);
    }

    /** @test */
    public function 存在しない通知idで既読にしようとすると404エラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('notifications.read', 999999));

        $response->assertNotFound();
    }
}
