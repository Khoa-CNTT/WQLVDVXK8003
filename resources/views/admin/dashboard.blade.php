@extends('layouts.admin')

@section('title', 'Dashboard - Phương Thanh Express')

@section('page-title', 'Tổng quan hệ thống')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Thống kê vé đã bán -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Vé đã bán</h3>
                    <p class="text-2xl font-semibold">{{ number_format($tickets_sold) }}</p>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-500">↑ {{ $tickets_growth }}%</span>
                <span class="text-gray-500 text-sm">so với tháng trước</span>
            </div>
        </div>

        <!-- Doanh thu -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Doanh thu</h3>
                    <p class="text-2xl font-semibold">{{ number_format($revenue) }} đ</p>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-500">↑ {{ $revenue_growth }}%</span>
                <span class="text-gray-500 text-sm">so với tháng trước</span>
            </div>
        </div>

        <!-- Người dùng mới -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Người dùng mới</h3>
                    <p class="text-2xl font-semibold">{{ $new_users }}</p>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-500">↑ {{ $users_growth }}%</span>
                <span class="text-gray-500 text-sm">so với tháng trước</span>
            </div>
        </div>

        <!-- Chuyến xe hoạt động -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-gray-500 text-sm">Chuyến xe hoạt động</h3>
                    <p class="text-2xl font-semibold">{{ $active_trips }}</p>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-yellow-500">● {{ $active_trips_percent }}%</span>
                <span class="text-gray-500 text-sm">tỷ lệ hoạt động</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Biểu đồ doanh thu theo tháng -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Doanh thu theo tháng</h3>
            <canvas id="revenueChart" height="300"></canvas>
        </div>

        <!-- Biểu đồ lượng vé đã bán theo tuyến -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Lượng vé đã bán theo tuyến</h3>
            <canvas id="routeChart" height="300"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Các chuyến xe gần nhất -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4">Các chuyến xe sắp khởi hành</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">ID</th>
                            <th class="py-3 px-6 text-left">Tuyến</th>
                            <th class="py-3 px-6 text-left">Giờ khởi hành</th>
                            <th class="py-3 px-6 text-left">Ngày</th>
                            <th class="py-3 px-6 text-left">Tài xế</th>
                            <th class="py-3 px-6 text-center">Trạng thái</th>
                            <th class="py-3 px-6 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm">
                        @foreach($upcoming_trips as $trip)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="py-3 px-6 text-left">{{ $trip->id }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->departure }} - {{ $trip->destination }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->departure_time }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->departure_date }}</td>
                            <td class="py-3 px-6 text-left">{{ $trip->driver_name }}</td>
                            <td class="py-3 px-6 text-center">
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                    {{ $trip->status }}
                                </span>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <a href="{{ route('admin.trips.show', $trip->id) }}" class="text-blue-600 hover:text-blue-900 mr-2">Xem</a>
                                <a href="{{ route('admin.trips.edit', $trip->id) }}" class="text-yellow-600 hover:text-yellow-900">Sửa</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Biểu đồ doanh thu theo tháng
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenue_chart['labels']) !!},
            datasets: [{
                label: 'Doanh thu (triệu đồng)',
                data: {!! json_encode($revenue_chart['data']) !!},
                backgroundColor: 'rgba(249, 115, 22, 0.2)',
                borderColor: 'rgba(249, 115, 22, 1)',
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Biểu đồ lượng vé đã bán theo tuyến
    const routeCtx = document.getElementById('routeChart').getContext('2d');
    const routeChart = new Chart(routeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($route_chart['labels']) !!},
            datasets: [{
                label: 'Số vé đã bán',
                data: {!! json_encode($route_chart['data']) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection
