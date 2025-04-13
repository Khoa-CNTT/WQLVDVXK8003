@extends('layouts.app')

@section('title', 'Đăng Nhập - Phương Thanh Express')

@section('styles')
body {
    background: linear-gradient(to right, #36D1DC, #5B86E5);
    overflow: hidden;
}
.login-container {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0px 15px 30px rgba(0, 0, 0, 0.2);
    max-width: 400px;
    text-align: center;
    animation: fadeIn 0.8s ease-in-out;
    position: relative;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.login-input {
    border: 2px solid #ddd;
    transition: all 0.3s ease-in-out;
}
.login-input:focus {
    border-color: #36D1DC;
    box-shadow: 0px 0px 10px rgba(54, 209, 220, 0.3);
}
.login-button {
    transition: all 0.3s ease-in-out;
}
.login-button:hover {
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
    <!-- Container đăng nhập -->
    <div class="login-container relative">
        <!-- Hình xe khách -->
        <img src="https://cdn-icons-png.flaticon.com/512/1283/1283310.png" class="bus-image">

        <h2 class="text-2xl font-bold text-gray-800 mt-10">🔑 Đăng Nhập</h2>
        <p class="text-gray-500">Vui lòng nhập thông tin tài khoản</p>

        <form method="POST" action="{{ route('login') }}" class="mt-6">
            @csrf
            <div class="mb-4">
                <input type="email" id="email" name="email" class="w-full p-3 border rounded-lg login-input @error('email') border-red-500 @enderror" placeholder="👤 Email đăng nhập" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <input type="password" id="password" name="password" class="w-full p-3 border rounded-lg login-input @error('password') border-red-500 @enderror" placeholder="🔑 Mật khẩu" required>
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4 flex items-center">
                <input type="checkbox" id="remember" name="remember" class="mr-2" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="text-sm text-gray-600">Ghi nhớ đăng nhập</label>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold login-button">🔓 Đăng nhập</button>
        </form>

        @if(session('error'))
            <p class="text-red-600 mt-3">⚠️ {{ session('error') }}</p>
        @endif

        <div class="mt-4 text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-blue-600">Quên mật khẩu?</a>
        </div>

        <p class="mt-4 text-center text-sm">Chưa có tài khoản? <a href="{{ route('register') }}" class="text-blue-600 font-bold">Đăng ký</a></p>
    </div>
</div>
@endsection
