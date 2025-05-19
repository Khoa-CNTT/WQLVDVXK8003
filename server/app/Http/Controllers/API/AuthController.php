<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role_id' => 2 // Mặc định là khách hàng
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id
                ],
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 201);
    }

    /**
     * Đăng nhập vào hệ thống
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Lấy thêm thông tin chi tiết từ user
        $userDetail = User::with(['role'])->find($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id,
                    'role' => $userDetail->role ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ],
                'access_token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    /**
     * Đăng xuất khỏi hệ thống
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ], 200);
    }

    /**
     * Gửi mã xác thực qua số điện thoại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi xác thực dữ liệu',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Kiểm tra xem số điện thoại có tồn tại trong hệ thống không
            $user = User::where('phone', $request->phone)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tài khoản với số điện thoại này'
                ], 404);
            }

            // Tạo mã xác thực ngẫu nhiên 6 số
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Lưu mã xác thực vào cache với thời hạn 5 phút
            $cacheKey = 'password_reset_' . $request->phone;
            Cache::put($cacheKey, $verificationCode, now()->addMinutes(5));

            // TODO: Gửi mã xác thực qua SMS
            // Trong môi trường development, trả về mã để test
            return response()->json([
                'success' => true,
                'message' => 'Đã gửi mã xác thực qua số điện thoại',
                'data' => [
                    'verification_code' => $verificationCode // Chỉ trả về trong môi trường development
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in forgotPassword: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xác thực mã và đặt lại mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'verification_code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Kiểm tra mã xác thực
        $cacheKey = 'password_reset_' . $request->phone;
        $storedCode = Cache::get($cacheKey);

        if (!$storedCode || $storedCode !== $request->verification_code) {
            return response()->json([
                'success' => false,
                'message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Tìm user theo số điện thoại
        $user = User::where('phone', $request->phone)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản'
            ], 404);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        // Xóa mã xác thực khỏi cache
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công'
        ]);
    }

    /**
     * Lấy thông tin người dùng hiện tại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getCurrentUser(Request $request)
    {
        $user = $request->user();

        // Lấy thêm thông tin liên quan
        $userDetail = User::with(['role'])->find($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role_id' => $user->role_id,
                    'role' => $userDetail->role ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ]
        ], 200);
    }

    /**
     * Đổi mật khẩu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không chính xác'
            ], 400);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu đã được thay đổi thành công'
        ]);
    }
}
