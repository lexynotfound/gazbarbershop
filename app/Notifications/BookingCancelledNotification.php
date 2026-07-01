<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking, public string $reason)
    {
        $this->afterCommit();
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
        return (new MailMessage)
            ->subject("Booking {$this->booking->booking_code} dibatalkan otomatis")
            ->greeting("Halo {$notifiable->name},")
            ->line($this->message())
            ->line('Jadwal sebelumnya: '.$this->booking->booking_start->translatedFormat('d F Y, H:i'))
            ->line('Capster: '.$this->booking->capster->name)
            ->action('Reschedule Booking', route('booking.reschedule.form', $this->booking))
            ->line('Silakan pilih jadwal baru yang masih tersedia.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'title' => 'Booking dibatalkan otomatis',
            'message' => $this->message(),
            'reason' => $this->reason,
            'previous_schedule' => $this->booking->booking_start->toIso8601String(),
            'action_label' => 'Reschedule Booking',
            'action_url' => route('booking.reschedule.form', $this->booking),
        ];
    }

    private function message(): string
    {
        return match ($this->reason) {
            'NO_CONFIRMATION' => 'Booking dibatalkan karena konfirmasi kehadiran tidak diterima dalam 15 menit.',
            'LATE_ARRIVAL' => 'Booking dibatalkan karena pelanggan belum check-in lebih dari 15 menit setelah jadwal.',
            default => 'Booking dibatalkan otomatis oleh sistem.',
        };
    }
}
