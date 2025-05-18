<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $vnpayConfig;
    protected $momoConfig;

    public function __construct()
    {
        $this->vnpayConfig = [
            'url' => config('services.vnpay.url'),
            'tmnCode' => config('services.vnpay.tmn_code'),
            'hashSecret' => config('services.vnpay.hash_secret'),
            'returnUrl' => config('services.vnpay.return_url'),
        ];

        $this->momoConfig = [
            'url' => config('services.momo.url'),
            'partnerCode' => config('services.momo.partner_code'),
            'accessKey' => config('services.momo.access_key'),
            'secretKey' => config('services.momo.secret_key'),
            'returnUrl' => config('services.momo.return_url'),
        ];
    }

    public function processPayment(Payment $payment)
    {
        try {
            switch ($payment->method) {
                case 'vnpay':
                    return $this->processVnpayPayment($payment);
                case 'momo':
                    return $this->processMomoPayment($payment);
                case 'cash':
                    return $this->processCashPayment($payment);
                default:
                    throw new \Exception('Invalid payment method');
            }
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'payment_id' => $payment->id,
                'method' => $payment->method,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function processVnpayPayment(Payment $payment)
    {
        $vnp_Url = $this->vnpayConfig['url'];
        $vnp_HashSecret = $this->vnpayConfig['hashSecret'];
        $vnp_TmnCode = $this->vnpayConfig['tmnCode'];
        $vnp_ReturnUrl = $this->vnpayConfig['returnUrl'];

        $vnp_TxnRef = $payment->id . '-' . time();
        $vnp_OrderInfo = "Thanh toan ve xe Phuong Thanh Express";
        $vnp_Amount = $payment->amount * 100;
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

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

        return [
            'success' => true,
            'payment_url' => $vnp_Url
        ];
    }

    protected function processMomoPayment(Payment $payment)
    {
        $endpoint = $this->momoConfig['url'];
        $partnerCode = $this->momoConfig['partnerCode'];
        $accessKey = $this->momoConfig['accessKey'];
        $secretKey = $this->momoConfig['secretKey'];
        $returnUrl = $this->momoConfig['returnUrl'];

        $orderId = $payment->id . '-' . time();
        $orderInfo = "Thanh toan ve xe Phuong Thanh Express";
        $amount = (string)$payment->amount;
        $requestId = (string)$payment->id;
        $extraData = "";

        $rawHash = "partnerCode=" . $partnerCode .
            "&accessKey=" . $accessKey .
            "&requestId=" . $requestId .
            "&amount=" . $amount .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&returnUrl=" . $returnUrl .
            "&notifyUrl=" . $returnUrl .
            "&extraData=" . $extraData;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'returnUrl' => $returnUrl,
            'notifyUrl' => $returnUrl,
            'extraData' => $extraData,
            'requestType' => 'captureMoMoWallet',
            'signature' => $signature
        ];

        $response = Http::post($endpoint, $data);
        $result = $response->json();

        if ($result['errorCode'] === 0) {
            return [
                'success' => true,
                'payment_url' => $result['payUrl']
            ];
        }

        return [
            'success' => false,
            'message' => $result['message'] ?? 'Momo payment failed'
        ];
    }

    protected function processCashPayment(Payment $payment)
    {
        // For cash payments, we just mark it as pending for manual confirmation
        return [
            'success' => true,
            'message' => 'Cash payment recorded, waiting for confirmation'
        ];
    }
}
