import React, { useEffect, useState } from 'react';
import './Dashboard.css';
import { Link } from 'react-router-dom';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar, Pie } from 'react-chartjs-2';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import { Storage } from '../../../../constant/storage';
import axios from 'axios';

// Đăng ký các thành phần ChartJS
ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

const Dashboard = () => {
    const [currentDateTime, setCurrentDateTime] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    // States cho dữ liệu thống kê
    const [tripCount, setTripCount] = useState(0);
    const [ticketCount, setTicketCount] = useState(0);
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
        const authData = localStorage.getItem(Storage.AUTH_DATA);
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
                const config = {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };
                // Gọi API thật lấy dữ liệu dashboard
                const res = await axios.get('http://127.0.0.1:8000/api/v1/admin/dashboard', config);
                // Sửa lại key lấy dữ liệu cho đúng backend
                setTripCount(res.data.todayTripCount ?? 0);
                setRoutesCount(res.data.linesCount ?? 0);
                setTicketCount(res.data.ticketCount ?? 0);
                setDriversCount(res.data.driversCount ?? 0);
                if (res.data.driverExperienceData) setDriverExperienceData(res.data.driverExperienceData);
                if (res.data.vehicleData) setVehicleData(res.data.vehicleData);
                setLoading(false);
            } catch (err) {
                console.error('Error fetching dashboard data:', err);
                setError(err.message || 'Đã xảy ra lỗi khi tải dữ liệu');
                // Fallback về dữ liệu mẫu nếu lỗi
                setTripCount(0);
                setTicketCount(0);
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
            }
        };
        fetchDashboardData();
    }, []);

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
                                <Link to="/admin/trips" className="stat-card-link">
                                    <div className="stat-card">
                                        <h3 className="card-title">Chuyến xe</h3>
                                        <p className="card-value">{tripCount}</p>
                                        <p className="card-description">đang vận hành</p>
                                    </div>
                                </Link>
                                <Link to="/admin" className="stat-card-link">
                                    <div className="stat-card">
                                        <h3 className="card-title">Số vé</h3>
                                        <p className="card-value">{ticketCount}</p>
                                        <p className="card-description">đã bán</p>
                                    </div>
                                </Link>
                                <Link to="/admin/line" className="stat-card-link">
                                    <div className="stat-card">
                                        <h3 className="card-title">Tuyến đường</h3>
                                        <p className="card-value">{routesCount}</p>
                                        <p className="card-description">đang khai thác</p>
                                    </div>
                                </Link>
                                <Link to="/admin/drivers" className="stat-card-link">
                                    <div className="stat-card">
                                        <h3 className="card-title">Tài xế</h3>
                                        <p className="card-value">{driversCount}</p>
                                        <p className="card-description">đang hoạt động</p>
                                    </div>
                                </Link>
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
        </HomeAdminLayout>

    );
};

export default Dashboard;