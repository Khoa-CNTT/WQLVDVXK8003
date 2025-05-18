<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\BookingProcessed;
use Illuminate\Support\Facades\Log;

class ProcessBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $paymentMethod;

    public function __construct(Booking $booking, string $paymentMethod)
    {
        $this->booking = $booking;
        $this->paymentMethod = $paymentMethod;
    }

    public function handle(PaymentService $paymentService)
    {
        try {
            Log::info('Starting to process booking', ['booking_id' => $this->booking->id]);

            // Check seat availability
            if (!$this->booking->checkSeatsAvailability()) {
                throw new \Exception('Selected seats are no longer available');
            }

            // Create payment
            $payment = Payment::create([
                'booking_id' => $this->booking->id,
                'amount' => $this->booking->total_amount,
                'method' => $this->paymentMethod,
                'status' => 'pending'
            ]);

            // Process payment based on method
            $paymentResult = $paymentService->processPayment($payment);

            if ($paymentResult['success']) {
                $this->booking->update(['status' => 'confirmed']);
                $payment->update(['status' => 'completed']);

                // Create tickets
                $this->booking->createTickets();

                // Notify customer
                event(new BookingProcessed($this->booking));
            } else {
                $this->booking->update(['status' => 'payment_failed']);
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $paymentResult['message']
                ]);
            }

            Log::info('Finished processing booking', [
                'booking_id' => $this->booking->id,
                'status' => $this->booking->status
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing booking', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage()
            ]);

            $this->booking->update(['status' => 'failed']);
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Booking job failed', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage()
        ]);

        $this->booking->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage()
        ]);
    }
}
