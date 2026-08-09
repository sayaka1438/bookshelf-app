<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private ReadingPlan $readingPlan,
        private string $timing,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => '読書計画リマインダー',
            'body' => match ($this->timing) {
                'three_days_before' => "『{$this->readingPlan->book->title}』の期日まであと3日です。",
                'on_due_date' => "『{$this->readingPlan->book->title}』が本日期日です。",
                'three_days_after' => "『{$this->readingPlan->book->title}』の期日を3日過ぎています。",
                default => '読書計画の通知です。'
            },
            'timing' => $this->timing,
        ];
    }
}
