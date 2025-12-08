<?php

namespace App\Notifications;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetAlertNotification extends Notification
{
    use Queueable;

    protected Budget $budget;
    protected string $alertLevel;
    protected float $percentage;

    /**
     * Create a new notification instance.
     */
    public function __construct(Budget $budget, string $alertLevel, float $percentage)
    {
        $this->budget = $budget;
        $this->alertLevel = $alertLevel;
        $this->percentage = $percentage;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = match ($this->alertLevel) {
            'exceeded' => '🚨 Budget Exceeded!',
            'critical' => '⚠️ Budget Critical - 90% Used',
            'warning' => '📊 Budget Warning - 70% Used',
            default => '📊 Budget Update',
        };

        $color = match ($this->alertLevel) {
            'exceeded' => 'error',
            'critical' => 'error',
            'warning' => 'warning',
            default => 'success',
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Hello {$notifiable->name}!")
            ->line($this->getMessageLine())
            ->line("Budget: NPR " . number_format($this->budget->monthly_limit, 2))
            ->line("Spent: NPR " . number_format($this->budget->spent, 2))
            ->line("Remaining: NPR " . number_format($this->budget->remaining, 2))
            ->action('View Dashboard', url('/admin'))
            ->line('Consider reviewing your spending habits.');
    }

    /**
     * Get the array representation of the notification for database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'title' => $this->getTitle(),
            'body' => $this->getMessageLine(),
            'icon' => $this->getIcon(),
            'iconColor' => $this->getIconColor(),
            'budget_id' => $this->budget->id,
            'month' => $this->budget->month,
            'percentage' => $this->percentage,
            'alert_level' => $this->alertLevel,
        ];
    }

    protected function getTitle(): string
    {
        return match ($this->alertLevel) {
            'exceeded' => 'Budget Exceeded!',
            'critical' => 'Budget Critical Alert',
            'warning' => 'Budget Warning',
            default => 'Budget Update',
        };
    }

    protected function getMessageLine(): string
    {
        return match ($this->alertLevel) {
            'exceeded' => "You've exceeded your monthly budget! You've spent " . number_format($this->percentage, 1) . "% of your limit.",
            'critical' => "You've used 90% of your monthly budget. Only NPR " . number_format($this->budget->remaining, 2) . " remaining.",
            'warning' => "You've used 70% of your monthly budget. NPR " . number_format($this->budget->remaining, 2) . " remaining.",
            default => "Your budget is at " . number_format($this->percentage, 1) . "%.",
        };
    }

    protected function getIcon(): string
    {
        return match ($this->alertLevel) {
            'exceeded' => 'heroicon-o-exclamation-circle',
            'critical' => 'heroicon-o-exclamation-triangle',
            'warning' => 'heroicon-o-bell-alert',
            default => 'heroicon-o-information-circle',
        };
    }

    protected function getIconColor(): string
    {
        return match ($this->alertLevel) {
            'exceeded' => 'danger',
            'critical' => 'danger',
            'warning' => 'warning',
            default => 'info',
        };
    }
}
