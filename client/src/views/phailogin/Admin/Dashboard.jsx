import React, { useEffect, useState } from 'react';
import './Dashboard.css';
import { Link } from 'react-router-dom';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar, Pie } from 'react-chartjs-2';

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
        <div className="dashboard-container">
            {/* Sidebar */}
            <aside className="sidebar">
                <div className="company-logo">
                    <h1>Phương Thanh Express</h1>
                </div>
                <nav className="menu-list">
                    <Link to="/dashboard" className="menu-item active">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Thống kê</span>
                    </Link>
                    <Link to="/ticketCrud" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <span>Vé xe</span>
                    </Link>
                    <Link to="/vehicles" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        <span>Phương tiện</span>
                    </Link>
                    <Link to="/routes" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tuyến đường</span>
                    </Link>
                    <Link to="/trips" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h2a2 2 0 012 2v5m-4 0h4"></path>
                        </svg>
                        <span>Chuyến xe</span>
                    </Link>
                    <Link to="/drivers" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Tài xế</span>
                    </Link>
                    <Link to="/customers" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Khách Hàng</span>
                    </Link>
                    <Link to="/amenities" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tiện Ích</span>
                    </Link>
                </nav>
            </aside>

            {/* Nội dung chính */}
            <main className="main-content">
                {/* Header */}
                <header className="dashboard-header">
                    <div className="datetime">
                        {currentDateTime}
                    </div>
                    <div className="user-actions">
                        <span className="user-email">admin@phuongthanh.com</span>
                        <button className="logout-button" onClick={handleLogout}>Đăng Xuất</button>
                    </div>
                </header>

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
            </main>
        </div>
    );
};

export default Dashboard;