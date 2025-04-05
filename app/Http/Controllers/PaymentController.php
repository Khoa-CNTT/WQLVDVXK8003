<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function payVnpay(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        $ticket = Ticket::find($request->ticket_id);
        $amount = 500000; // Đổi theo giá trị của bạn

        // Lấy thông tin từ .env
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

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
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
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

        // Ghi log URL gửi VNPAY
        Log::info("VNPAY Payment URL: " . $paymentUrl);
        //2c92cb99c91535e072cb1bc5794cb8a727c2dec00c6fbe0c85a44541ba59f4230dc29dbbb64bbe1da7be634069810c72be75a9207651cdac63540b5d677dcb8b
        //c96b529c62c071e91d6d63dcb393ac31cabee912cdcbab822a82f4a1c11165dae0c295cf09460b79dbbf1fcef85b1afdd2361bb75100977848f81b93c00b6e1e
        return response()->json(['payment_url' => $paymentUrl]);
    }
    public function vnpayReturn(Request $request)
    {
        // Xử lý dữ liệu trả về từ VNPay
        return response()->json([
            'message' => 'VNPay Return',
            'data' => $request->all()
        ]);
    }
}
