<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The booking instance.
     *
     * @var \App\Models\Booking
     */
    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $url = url("/bookings/{$this->booking->id}");

        return (new MailMessage)
            ->subject('Xác nhận đặt vé - Phương Thanh Express')
            ->greeting("Xin chào {$notifiable->name},")
            ->line('Cảm ơn bạn đã đặt vé tại Phương Thanh Express.')
            ->line('Chi tiết đặt vé của bạn:')
            ->line("Mã đặt vé: {$this->booking->id}")
            ->line("Tuyến xe: {$this->booking->trip->line->name}")
            ->line("Thời gian khởi hành: {$this->booking->trip->departure_time}")
            ->line("Số ghế: " . implode(', ', $this->booking->seats->pluck('name')->toArray()))
            ->line("Tổng tiền: " . number_format($this->booking->total_amount) . ' VNĐ')
            ->action('Xem chi tiết đặt vé', $url)
            ->line('Nếu bạn có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.');
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
            'trip_id' => $this->booking->trip_id,
            'status' => $this->booking->status,
            'amount' => $this->booking->total_amount,
            'seats' => $this->booking->seats->pluck('name')->toArray(),
        ];
    }
}
