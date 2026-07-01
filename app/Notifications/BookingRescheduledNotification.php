<?php

namespace App\Notifications;

use App\Models\Booking;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Booking $booking, public CarbonInterface $previousBookingStart)
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
            ->subject("Booking {$this->booking->booking_code} dijadwalkan ulang")
            ->greeting("Halo {$notifiable->name},")
            ->line("Booking Anda dengan kode {$this->booking->booking_code} telah dijadwalkan ulang.")
            ->line('Jadwal sebelumnya: '.$this->previousBookingStart->translatedFormat('d F Y, H:i'))
            ->line('Jadwal baru: '.$this->booking->booking_start->translatedFormat('d F Y, H:i'))
            ->line('Capster: '.$this->booking->capster->name)
            ->line('Total Harga: Rp'.number_format($this->booking->grand_total, 0, ',', '.'))
            ->action('Lihat Booking', route('booking.show', $this->booking))
            ->line('Mohon datang maksimal 15 menit sebelum jadwal dimulai.');
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
            'title' => 'Booking dijadwalkan ulang',
            'message' => 'Jadwal booking Anda telah diubah.',
            'previous_schedule' => $this->previousBookingStart->toIso8601String(),
            'new_schedule' => $this->booking->booking_start->toIso8601String(),
            'action_label' => 'Lihat Booking',
            'action_url' => route('booking.show', $this->booking),
        ];
    }
}
