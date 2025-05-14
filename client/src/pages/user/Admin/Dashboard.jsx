import React, { useEffect, useState } from 'react';
import './Dashboard.css';
import { Link } from 'react-router-dom';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar, Pie } from 'react-chartjs-2';
import HomeAdminLayout from '../../../layouts/AdminLayout';

// Đăng ký các thành phần ChartJS
ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

const Dashboard = () => {
    const [currentDateTime, setCurrentDateTime] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // States cho dữ liệu thống kê
    const [tripCount, setTripCount] = useState(0);
    const [amenitiesCount, setAmenitiesCount] = useState(9);
    const [routesCount, setRoutesCount] = useState(9);
    const [driversCount, setDriversCount] = useState(10);

    // States cho dữ liệu biểu đồ
    const [driverExperienceData, setDriverExperienceData] = useState({
        labels: ['1 năm', '3 năm', '5 năm', '7 năm', '9 năm'],
        datasets: [{
            label: 'Số tài xế',
            data: [3, 5, 2, 8, 4],
            backgroundColor: '#f97316',
            borderColor: '#ea580c',
            borderWidth: 1
        }]
    });

    const [vehicleData, setVehicleData] = useState({
        labels: ['Xe Vip', 'Xe LimousineVip', 'Xe tiêu chuẩn', 'Xe VIP Đà Nẵng', 'Kim Đức Hiền', 'Sơn Tùng'],
        datasets: [{
            label: 'Phương tiện',
            data: [5, 3, 2, 4, 1, 2],
            backgroundColor: [
                '#3b82f6',
                '#ef4444',
                '#facc15',
                '#22c55e',
                '#f97316',
                '#10b981'
            ],
            borderWidth: 1
        }]
    });

    // Hàm lấy token từ localStorage
    const getToken = () => {
        const authData = localStorage.getItem('authData');
        if (authData) {
            try {
                const parsed = JSON.parse(authData);
                return parsed.data?.access_token;
            } catch (error) {
                console.error('Error parsing auth data:', error);
                return null;
            }
        }
        return null;
    };

    // Hàm cập nhật thời gian
    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            const date = now.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const time = now.toLocaleTimeString('vi-VN', { hour12: false });
            setCurrentDateTime(`${date} | ${time}`);
        };

        // Cập nhật thời gian mỗi giây
        const timeInterval = setInterval(updateTime, 1000);
        updateTime(); // Cập nhật ngay lập tức

        // Xóa interval khi component unmount
        return () => clearInterval(timeInterval);
    }, []);

    // Lấy dữ liệu thống kê từ API
    useEffect(() => {
        const fetchDashboardData = async () => {
            try {
                setLoading(true);
                const token = getToken();

                if (!token) {
                    throw new Error('Bạn chưa đăng nhập hoặc phiên đăng nhập đã hết hạn');
                }

                // Tạo headers với token xác thực
                const config = {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };

                // Sử dụng dữ liệu mẫu khi chưa có API hoạt động
                setTripCount(0);
                setAmenitiesCount(9);
                setRoutesCount(9);
                setDriversCount(10);

                setDriverExperienceData({
                    labels: ['1 năm', '3 năm', '5 năm', '7 năm', '9 năm'],
                    datasets: [{
                        label: 'Số tài xế',
                        data: [3, 5, 2, 8, 4],
                        backgroundColor: '#f97316',
                        borderColor: '#ea580c',
                        borderWidth: 1
                    }]
                });

                setVehicleData({
                    labels: ['Xe Vip', 'Xe LimousineVip', 'Xe tiêu chuẩn', 'Xe VIP Đà Nẵng', 'Kim Đức Hiền', 'Sơn Tùng'],
                    datasets: [{
                        label: 'Phương tiện',
                        data: [5, 3, 2, 4, 1, 2],
                        backgroundColor: [
                            '#3b82f6',
                            '#ef4444',
                            '#facc15',
                            '#22c55e',
                            '#f97316',
                            '#10b981'
                        ],
                        borderWidth: 1
                    }]
                });

                setLoading(false);
            } catch (err) {
                console.error('Error fetching dashboard data:', err);
                setError(err.message || 'Đã xảy ra lỗi khi tải dữ liệu');
                setLoading(false);
            }
        };

        fetchDashboardData();
    }, []);

    // Hàm đăng xuất
    const handleLogout = () => {
        if (window.confirm('Bạn có chắc muốn đăng xuất?')) {
            localStorage.removeItem('authData');
            localStorage.removeItem('userInfo');
            window.location.href = '/login';
        }
    };

    // Tùy chọn biểu đồ cột
    const barOptions = {
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Số tài xế'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Kinh nghiệm (năm)'
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        },
        maintainAspectRatio: false
    };

    // Tùy chọn biểu đồ tròn
    const pieOptions = {
        plugins: {
            legend: {
                position: 'right'
            }
        },
        maintainAspectRatio: false
    };

    return (
        <HomeAdminLayout>

            <div className="dashboard-container">
                {/* Nội dung Dashboard */}
                <div className="dashboard-content">
                    <h2 className="section-title">Thống Kê</h2>

                    {loading ? (
                        <div className="loading-indicator">Đang tải dữ liệu...</div>
                    ) : error ? (
                        <div className="error-message">
                            <p>{error}</p>
                            <p>Sử dụng dữ liệu mẫu để hiển thị.</p>
                        </div>
                    ) : (
                        <>
                            {/* Cards - 4 ô thống kê */}
                            <div className="stat-cards">
                                <div className="stat-card">
                                    <h3 className="card-title">Chuyến xe</h3>
                                    <p className="card-value">{tripCount}</p>
                                    <p className="card-description">đang vận hành</p>
                                </div>
                                <div className="stat-card">
                                    <h3 className="card-title">Tiện ích</h3>
                                    <p className="card-value">{amenitiesCount}</p>
                                    <p className="card-description">được cung cấp</p>
                                </div>
                                <div className="stat-card">
                                    <h3 className="card-title">Tuyến đường</h3>
                                    <p className="card-value">{routesCount}</p>
                                    <p className="card-description">đang khai thác</p>
                                </div>
                                <div className="stat-card">
                                    <h3 className="card-title">Tài xế</h3>
                                    <p className="card-value">{driversCount}</p>
                                    <p className="card-description">đang hoạt động</p>
                                </div>
                            </div>

                            {/* Biểu đồ */}
                            <div className="chart-container">
                                <div className="chart-box">
                                    <h3 className="chart-title">Kinh nghiệm tài xế</h3>
                                    <div className="chart">
                                        <Bar data={driverExperienceData} options={barOptions} />
                                    </div>
                                </div>
                                <div className="chart-box">
                                    <h3 className="chart-title">Tổng số phương tiện</h3>
                                    <div className="chart">
                                        <Pie data={vehicleData} options={pieOptions} />
                                    </div>
                                </div>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </HomeAdminLayout>

    );
};

export default Dashboard;