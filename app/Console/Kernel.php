<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Định nghĩa lịch chạy lệnh của ứng dụng.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Tự động hủy các đặt chỗ chưa thanh toán sau 30 phút
        $schedule->command('reservations:cancel-unpaid')->everyMinute();

        // Gửi email nhắc nhở cho các chuyến đi sắp tới
        $schedule->command('trips:send-reminders')->dailyAt('08:00');

        // Tạo báo cáo hàng ngày
        $schedule->command('reports:generate-daily')->dailyAt('23:59');
    }

    /**
     * Đăng ký các lệnh cho ứng dụng.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
