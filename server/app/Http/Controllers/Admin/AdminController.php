<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Khởi tạo controller với middleware
     */
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('admin');
    }

    /**
     * Trang dashboard chính
     */
    public function dashboard()
    {
        return redirect()->route('admin.dashboard');
    }

    /**
     * Trang hồ sơ cá nhân
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:20|unique:users,phone,'.$user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->back()->with('success', 'Hồ sơ đã được cập nhật thành công.');
    }

    /**
     * Thay đổi mật khẩu
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Mật khẩu đã được thay đổi thành công.');
    }
}
