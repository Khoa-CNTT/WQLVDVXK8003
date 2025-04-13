<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Process payment for a ticket.
     */
    public function process(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'payment_method' => 'required|in:vnpay,momo',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);

        // Check if the ticket belongs to the current user
        if (Auth::id() != $ticket->user_id) {
            return abort(403, 'Unauthorized action');
        }

        // Check if the ticket is already paid
        if ($ticket->payment && $ticket->payment->status == 'completed') {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('info', 'This ticket is already paid');
        }

        // Create a pending payment record
        $payment = Payment::updateOrCreate(
            ['ticket_id' => $ticket->id],
            [
                'payment_method' => $request->payment_method,
                'amount' => $ticket->price,
                'status' => 'pending',
                'notes' => 'Online payment',
            ]
        );

        // Process payment based on method
        if ($request->payment_method == 'vnpay') {
            return $this->processVnPay($ticket, $payment);
        } else if ($request->payment_method == 'momo') {
            return $this->processMomo($ticket, $payment);
        }

        return back()->withErrors(['message' => 'Invalid payment method']);
    }

    /**
     * Process payment with VNPay.
     */
    private function processVnPay($ticket, $payment)
    {
        // Generate VNPay payment URL
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $vnp_Returnurl = route('payments.callback.vnpay');
        $vnp_TmnCode = env('VNPAY_TMN_CODE', 'YOUR_MERCHANT_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET', 'YOUR_SECRET_KEY');

        $vnp_TxnRef = $payment->id . '-' . time();
        $vnp_OrderInfo = "Payment for ticket " . $ticket->ticket_number;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $payment->amount * 100; // VNPay requires amount in VND with no decimal
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = request()->ip();
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
        ];

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        // Update payment with reference ID
        $payment->transaction_id = $vnp_TxnRef;
        $payment->save();

        // Redirect to VNPay payment gateway
        return redirect($vnp_Url);
    }

    /**
     * Process payment with MoMo.
     */
    private function processMomo($ticket, $payment)
    {
        // For demonstration purposes only
        // In a real application, you would integrate with MoMo's API

        // Simulate successful payment
        $payment->status = 'completed';
        $payment->transaction_id = 'MOMO' . time();
        $payment->paid_at = now();
        $payment->save();

        // Update ticket status
        $ticket->status = 'confirmed';
        $ticket->save();

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Payment successful');
    }

    /**
     * Handle VNPay payment callback.
     */
    public function callbackVnpay(Request $request)
    {
        // Get payment data
        $vnp_ResponseCode = $request->vnp_ResponseCode;
        $vnp_TxnRef = $request->vnp_TxnRef;
        $vnp_Amount = $request->vnp_Amount;
        $vnp_TransactionNo = $request->vnp_TransactionNo;
        $vnp_BankCode = $request->vnp_BankCode;
        $vnp_BankTranNo = $request->vnp_BankTranNo;

        // Validate response
        if (empty($vnp_TxnRef)) {
            return redirect()->route('home')
                ->withErrors(['message' => 'Payment failed']);
        }

        // Extract payment ID from txnRef
        $paymentId = explode('-', $vnp_TxnRef)[0];
        $payment = Payment::find($paymentId);

        if (!$payment) {
            return redirect()->route('home')
                ->withErrors(['message' => 'Payment not found']);
        }

        // Check payment status
        if ($vnp_ResponseCode == '00') {
            // Payment successful
            $payment->status = 'completed';
            $payment->transaction_id = $vnp_TransactionNo;
            $payment->paid_at = now();
            $payment->notes = 'VNPay payment: Bank code ' . $vnp_BankCode . ', Bank transaction ' . $vnp_BankTranNo;
            $payment->save();

            // Update ticket status
            $ticket = $payment->ticket;
            $ticket->status = 'confirmed';
            $ticket->save();

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Payment successful');
        } else {
            // Payment failed
            $payment->status = 'failed';
            $payment->notes = 'VNPay payment failed: ' . $vnp_ResponseCode;
            $payment->save();

            return redirect()->route('payments.process', [
                'ticket_id' => $payment->ticket_id,
                'payment_method' => $payment->payment_method,
            ])->withErrors(['message' => 'Payment failed. Please try again.']);
        }
    }
}
