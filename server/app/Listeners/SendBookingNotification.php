<?php

namespace App\Listeners;

use App\Events\BookingProcessed;
use App\Notifications\BookingConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBookingNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(BookingProcessed $event): void
    {
        try {
            $booking = $event->booking;
            $user = $booking->user;

            // Send email notification
            $user->notify(new BookingConfirmation($booking));

            Log::info('Booking notification sent', [
                'booking_id' => $booking->id,
                'user_id' => $user->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send booking notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            $this->fail($e);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(BookingProcessed $event, \Throwable $exception): void
    {
        Log::error('Booking notification job failed', [
            'booking_id' => $event->booking->id,
            'error' => $exception->getMessage()
        ]);
    }
}
