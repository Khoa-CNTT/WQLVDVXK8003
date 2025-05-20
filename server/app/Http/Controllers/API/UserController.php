<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Hiển thị thông tin người dùng hiện tại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        // Lấy thông tin booking và ticket
        $bookingCount = $user->bookings()->count();
        $ticketCount = $user->bookings()->withCount('tickets')->get()->sum('tickets_count');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => [
                    'booking_count' => $bookingCount,
                    'ticket_count' => $ticketCount
                ]
            ]
        ]);
    }

    /**
     * Cập nhật thông tin người dùng hiện tại
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        // Nếu có mật khẩu hiện tại, kiểm tra xác thực
        if ($request->has('current_password') && $request->current_password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không chính xác'
                ], 400);
            }
        }

        // Cập nhật thông tin
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->email = $request->email;

        // Nếu có mật khẩu mới
        if ($request->has('password') && $request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công',
            'data' => $user
        ]);
    }

    /**
     * Hiển thị danh sách người dùng (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Tìm kiếm theo tên, email, phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        $users = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Tạo người dùng mới (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,banned',
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
            'role_id' => $request->role_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo người dùng thành công',
            'data' => $user
        ], 201);
    }

    /**
     * Hiển thị chi tiết người dùng (chỉ dành cho admin)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with(['role', 'bookings'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Cập nhật thông tin người dùng (chỉ dành cho admin)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateLate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,banned',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role_id = $request->role_id;
        $user->status = $request->status;

        // Nếu có mật khẩu mới
        if ($request->has('password') && $request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công',
            'data' => $user
        ]);
    }

    /**
     * Xóa người dùng (chỉ dành cho admin)
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Sử dụng Request để lấy người dùng đang xác thực
            $currentUserId = request()->user() ? request()->user()->id : null;

            // Nếu có người dùng đang đăng nhập và ID khớp với ID đang xóa
            if ($currentUserId && $id == $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tài khoản đang đăng nhập'
                ], 400);
            }

            $user = User::findOrFail($id);

            // Kiểm tra lịch sử đặt vé
            $hasBookings = $user->bookings()->exists();

            if ($hasBookings) {
                // Thay vì xóa, chỉ đánh dấu là inactive
                $user->status = 'inactive';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng đã được vô hiệu hóa vì có lịch sử đặt vé'
                ]);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công'
            ]);
        } catch (\Exception $e) {
            // Xử lý ngoại lệ, bỏ qua phần kiểm tra người dùng hiện tại
            $user = User::findOrFail($id);

            // Kiểm tra lịch sử đặt vé
            $hasBookings = $user->bookings()->exists();

            if ($hasBookings) {
                // Thay vì xóa, chỉ đánh dấu là inactive
                $user->status = 'inactive';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Người dùng đã được vô hiệu hóa vì có lịch sử đặt vé'
                ]);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công'
            ]);
        }
    }
}
