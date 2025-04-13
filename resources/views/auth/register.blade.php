@extends('layouts.app')

@section('title', 'Đăng Ký - Phương Thanh Express')

@section('styles')
body {
    background: linear-gradient(to right, #36D1DC, #5B86E5);
    overflow: hidden;
}
.register-container {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0px 15px 30px rgba(0, 0, 0, 0.2);
    max-width: 500px;
    text-align: center;
    animation: fadeIn 0.8s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.register-input {
    border: 2px solid #ddd;
    transition: all 0.3s ease-in-out;
}
.register-input:focus {
    border-color: #36D1DC;
    box-shadow: 0px 0px 10px rgba(54, 209, 220, 0.3);
}
.register-button {
    transition: all 0.3s ease-in-out;
}
.register-button:hover {
    transform: scale(1.05);
}
.bus-image {
    position: absolute;
    top: -60px;
    left: 50%;
    transform: translateX(-50%);
    width: 120px;
    animation: bounce 1.5s infinite alternate;
}
@keyframes bounce {
    from { transform: translateX(-50%) translateY(0); }
    to { transform: translateX(-50%) translateY(10px); }
}
@endsection

@section('content')
<div class="flex items-center justify-center min-h-screen p-6">
    <!-- Container đăng ký -->
    <div class="register-container relative">
        <!-- Hình xe khách -->
        <img src="https://cdn-icons-png.flaticon.com/512/1283/1283310.png" class="bus-image">

        <h2 class="text-2xl font-bold text-gray-800 mt-10">📝 Đăng Ký Tài Khoản</h2>
        <p class="text-gray-500">Vui lòng điền đầy đủ thông tin</p>

        <form method="POST" action="{{ route('register') }}" class="mt-6">
            @csrf
            <div class="mb-4">
                <input type="text" id="name" name="name" class="w-full p-3 border rounded-lg register-input @error('name') border-red-500 @enderror" placeholder="👤 Họ và tên" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <input type="email" id="email" name="email" class="w-full p-3 border rounded-lg register-input @error('email') border-red-500 @enderror" placeholder="📧 Email" value="{{ old('email') }}" required>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <input type="tel" id="phone" name="phone" class="w-full p-3 border rounded-lg register-input @error('phone') border-red-500 @enderror" placeholder="📱 Số điện thoại" value="{{ old('phone') }}" required>
                @error('phone')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg register-input @error('password') border-red-500 @enderror" placeholder="🔑 Mật khẩu" required>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full p-3 border rounded-lg register-input" placeholder="🔑 Xác nhận mật khẩu" required>
            </div>
            <div class="mb-4">
                <textarea id="address" name="address" class="w-full p-3 border rounded-lg register-input @error('address') border-red-500 @enderror" placeholder="🏠 Địa chỉ" rows="2">{{ old('address') }}</textarea>
                @error('address')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4 text-left">
                <label class="flex items-center">
                    <input type="checkbox" name="terms" id="terms" class="mr-2" required>
                    <span class="text-sm text-gray-600">Tôi đồng ý với <a href="{{ route('terms') }}" class="text-blue-600">điều khoản sử dụng</a> và <a href="{{ route('privacy') }}" class="text-blue-600">chính sách bảo mật</a></span>
                </label>
                @error('terms')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold register-button">📝 Đăng ký</button>
        </form>

        <p class="mt-4 text-center text-sm">Đã có tài khoản? <a href="{{ route('login') }}" class="text-blue-600 font-bold">Đăng nhập</a></p>
    </div>
</div>
@endsection
