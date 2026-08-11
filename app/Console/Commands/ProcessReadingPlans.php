<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class ProcessReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-reading-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限切れ更新とリマインダー通知を実行する';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->updateExpiredReadingPlans();

        $this->sendReminderNotifications(
            today()->addDays(3),
            'three_days_before',
        );

        $this->sendReminderNotifications(
            today(),
            'on_due_date',
        );

        $this->sendReminderNotifications(
            today()->subDays(3),
            'three_days_after',
        );
    }

    private function updateExpiredReadingPlans(): void
    {
        ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', today())
            ->update([
                'status' => ReadingPlanStatus::Expired,
            ]);
    }

    private function sendReminderNotifications(
        Carbon $targetDate,
        string $timing
    ): void {
        $status = $timing === 'three_days_after'
            ? ReadingPlanStatus::Expired
            : ReadingPlanStatus::InProgress;

        $plans = ReadingPlan::with(['book', 'user'])
            ->where('status', $status)
            ->whereDate('target_date', $targetDate)
            ->get();

        $plans->each(function (ReadingPlan $plan) use ($timing) {
            Notification::send(
                $plan->user,
                new ReadingPlanReminderNotification($plan, $timing),
            );
        });
    }
}
