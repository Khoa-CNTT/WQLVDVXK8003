<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

use App\Models\Ticket;
class PaymentController extends Controller
{
    /**
     * Tạo thanh toán VNPAY
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createVnpayPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $ticket = Booking::find($request->booking_id);
        $amount = $ticket->total_price; // Đổi theo giá trị của bạn

        // Lấy thông tin từ .env
        $vnp_Url = env('URL_VNPAY');
        $vnp_Returnurl = env('VNP_RETURN_URL','https://phuongthanh-express.com/payment-return');
        $vnp_TmnCode = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_TxnRef=time();
        // Tạo dữ liệu gửi VNPAY
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_BankCode"=>"VNBANK",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount * 100, // Đơn vị VND * 100
            "vnp_CurrCode" => "VND",
            "vnp_TxnRef" => time(), // Mã giao dịch duy nhất
            "vnp_OrderInfo" => "Thanh toan ve xe",
            "vnp_OrderType" => "billpayment",
            "vnp_Locale" => "vn",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_IpAddr" =>$request->ip(),
            "vnp_CreateDate" => date("YmdHis"),
            "vnp_ExpireDate" => date("YmdHis", strtotime("+1 day")),
        ];

        // Sắp xếp và tạo mã bảo mật
        ksort($inputData);
        // $hashdata = "";
        // foreach ($inputData as $key => $value) {
        //     $hashdata .= $key . "=" . $value . "&";
        // }
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
        // $hashdata = rtrim($hashdata, "&");


        // Tạo chữ ký bảo mật
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $paymentUrl = $vnp_Url . "?" . http_build_query($inputData) . "&vnp_SecureHash=" . $vnpSecureHash;

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $paymentUrl,
                'transaction_id' => $vnp_TxnRef
            ]
        ]);
    }

    /**
     * Tạo thanh toán MoMo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createMomoPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:bookings,id',
            'return_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra booking
        $booking = Booking::findOrFail($request->booking_id);

        // Chỉ cho phép thanh toán booking của chính mình
        if ($booking->user_id != request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền thanh toán đặt vé này'
            ], 403);
        }

        // Kiểm tra trạng thái thanh toán
        if ($booking->payment_status == 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Đặt vé này đã được thanh toán'
            ], 400);
        }

        // Cấu hình MoMo
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $orderInfo = "Thanh toán vé xe - " . $booking->booking_code;
        $amount = $booking->total_price;
        $orderId = $booking->booking_code . time();
        $redirectUrl = $request->return_url;
        $ipnUrl = route('api.payments.momo.ipn');
        $requestType = "payWithATM";
        $extraData = "";

        // Tạo chữ ký
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $orderId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "Phương Thanh Express",
            'storeId' => "PhuongThanhStore",
            'requestId' => $orderId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => "vi",
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        // Tạo payment record trong DB
        Payment::create([
            'booking_id' => $booking->id,
            'amount' => $booking->total_price,
            'payment_method' => 'momo',
            'transaction_id' => $orderId,
            'status' => 'pending',
            'payment_data' => json_encode($data),
        ]);

        // Gửi request đến MoMo
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);

        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result && $statusCode == 200) {
            $responseData = json_decode($result, true);
            if ($responseData['resultCode'] == 0) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'payment_url' => $responseData['payUrl'],
                        'transaction_id' => $orderId
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi tạo thanh toán: ' . $responseData['message']
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối đến cổng thanh toán MoMo'
            ], 500);
        }
    }

    /**
     * Xử lý callback từ cổng thanh toán
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentCallback(Request $request)
    {
        Log::info('Payment callback data', $request->all());

        // Xác định loại thanh toán
        $paymentMethod = $request->has('vnp_ResponseCode') ? 'vnpay' : 'momo';

        if ($paymentMethod == 'vnpay') {
            return $this->handleVnpayCallback($request);
        } else {
            return $this->handleMomoCallback($request);
        }
    }

    /**
     * Xử lý callback từ VNPAY
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function handleVnpayCallback(Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra chữ ký
        if ($secureHash != $vnp_SecureHash) {
            return response()->json([
                'success' => false,
                'message' => 'Chữ ký không hợp lệ'
            ], 400);
        }

        // Lấy thông tin giao dịch
        $transactionId = $inputData['vnp_TxnRef'];
        $responseCode = $inputData['vnp_ResponseCode'];

        // Tìm payment trong DB
        $payment = Payment::where('transaction_id', $transactionId)
                         ->where('payment_method', 'vnpay')
                         ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin thanh toán'
            ], 404);
        }

        // Cập nhật trạng thái thanh toán
        if ($responseCode == '00') {
            $payment->status = 'completed';
            $payment->response_data = json_encode($inputData);
            $payment->completed_at = now();
            $payment->save();

            // Cập nhật booking
            $booking = Booking::find($payment->booking_id);
            if ($booking) {
                $booking->payment_status = 'paid';
                $booking->status = 'confirmed';
                $booking->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'data' => [
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'transaction_id' => $transactionId
                ]
            ]);
        } else {
            $payment->status = 'failed';
            $payment->response_data = json_encode($inputData);
            $payment->save();

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán thất bại: ' . $this->getVnpayResponseMessage($responseCode),
                'data' => [
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'transaction_id' => $transactionId,
                    'response_code' => $responseCode
                ]
            ], 400);
        }
    }

    /**
     * Xử lý callback từ MoMo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function handleMomoCallback(Request $request)
    {
        $secretKey = env('MOMO_SECRET_KEY');
        $accessKey = env('MOMO_ACCESS_KEY');
        $partnerCode = env('MOMO_PARTNER_CODE');

        $inputData = $request->all();
        $partnerCode = $inputData['partnerCode'];
        $orderId = $inputData['orderId'];
        $requestId = $inputData['requestId'];
        $amount = $inputData['amount'];
        $orderInfo = $inputData['orderInfo'];
        $orderType = $inputData['orderType'];
        $transId = $inputData['transId'];
        $resultCode = $inputData['resultCode'];
        $message = $inputData['message'];
        $payType = $inputData['payType'];
        $responseTime = $inputData['responseTime'];
        $extraData = $inputData['extraData'];
        $signature = $inputData['signature'];

        // Tạo chữ ký để kiểm tra
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;
        $checkSignature = hash_hmac("sha256", $rawHash, $secretKey);

        // Kiểm tra chữ ký
        if ($checkSignature != $signature) {
            return response()->json([
                'success' => false,
                'message' => 'Chữ ký không hợp lệ'
            ], 400);
        }

        // Tìm payment trong DB
        $payment = Payment::where('transaction_id', $orderId)
                         ->where('payment_method', 'momo')
                         ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin thanh toán'
            ], 404);
        }

        // Cập nhật trạng thái thanh toán
        if ($resultCode == '0') {
            $payment->status = 'completed';
            $payment->response_data = json_encode($inputData);
            $payment->completed_at = now();
            $payment->save();

            // Cập nhật booking
            $booking = Booking::find($payment->booking_id);
            if ($booking) {
                $booking->payment_status = 'paid';
                $booking->status = 'confirmed';
                $booking->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công',
                'data' => [
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'transaction_id' => $transId
                ]
            ]);
        } else {
            $payment->status = 'failed';
            $payment->response_data = json_encode($inputData);
            $payment->save();

            return response()->json([
                'success' => false,
                'message' => 'Thanh toán thất bại: ' . $message,
                'data' => [
                    'booking_id' => $payment->booking_id,
                    'amount' => $payment->amount,
                    'transaction_id' => $transId,
                    'response_code' => $resultCode
                ]
            ], 400);
        }
    }

    /**
     * Lấy thông báo lỗi từ mã phản hồi VNPAY
     *
     * @param  string  $responseCode
     * @return string
     */
    private function getVnpayResponseMessage($responseCode)
    {
        $messages = [
            '00' => 'Giao dịch thành công',
            '01' => 'Giao dịch đã tồn tại',
            '02' => 'Merchant không hợp lệ',
            '03' => 'Dữ liệu gửi sang không đúng định dạng',
            '04' => 'Khởi tạo GD không thành công do Website đang bị tạm khoá',
            '05' => 'Giao dịch không thành công do: Quý khách nhập sai mật khẩu quá số lần quy định',
            '06' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu',
            '07' => 'Giao dịch bị nghi ngờ là gian lận',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày',
            '75' => 'Ngân hàng thanh toán đang bảo trì',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán nhiều lần',
            '99' => 'Lỗi không xác định',
        ];

        return isset($messages[$responseCode]) ? $messages[$responseCode] : 'Lỗi không xác định';
    }
}
