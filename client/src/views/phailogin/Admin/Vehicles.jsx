import React, { useState, useEffect } from 'react';
import './Vehicles.css';

const Vehicles = () => {
    // Mock vehicle data
    const [vehicles, setVehicles] = useState([
        { id: 1, plate: "51B-12345", type: "Xe khách", seats: 45, year: 2018, status: "Hoạt động" },
        { id: 2, plate: "51B-67890", type: "Xe giường nằm", seats: 40, year: 2020, status: "Bảo trì" },
        { id: 3, plate: "51B-54321", type: "Xe khách", seats: 50, year: 2019, status: "Hoạt động" }
    ]);

    const [currentTime, setCurrentTime] = useState('');
    const [currentDate, setCurrentDate] = useState('');
    const [showModal, setShowModal] = useState(false);
    const [editingVehicleId, setEditingVehicleId] = useState(null);

    // Form state
    const [vehiclePlate, setVehiclePlate] = useState('');
    const [vehicleType, setVehicleType] = useState('');
    const [vehicleSeats, setVehicleSeats] = useState('');
    const [vehicleYear, setVehicleYear] = useState('');
    const [vehicleStatus, setVehicleStatus] = useState('');

    // Action log data (loaded from localStorage or initialized)
    const [actionLog, setActionLog] = useState(() => {
        const savedLog = localStorage.getItem('actionLog');
        return savedLog ? JSON.parse(savedLog) : [];
    });

    // Update time regularly
    useEffect(() => {
        const updateTime = () => {
            const now = new Date();
            const date = now.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
            const time = now.toLocaleTimeString('vi-VN', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
            setCurrentDate(date);
            setCurrentTime(time);
        };

        updateTime();
        const interval = setInterval(updateTime, 1000);
        return () => clearInterval(interval);
    }, []);

    const openAddVehicleModal = () => {
        setEditingVehicleId(null);
        resetForm();
        setShowModal(true);
    };

    const resetForm = () => {
        setVehiclePlate('');
        setVehicleType('');
        setVehicleSeats('');
        setVehicleYear('');
        setVehicleStatus('');
    };

    const editVehicle = (id) => {
        const vehicle = vehicles.find(v => v.id === id);
        if (vehicle) {
            setEditingVehicleId(id);
            setVehiclePlate(vehicle.plate);
            setVehicleType(vehicle.type);
            setVehicleSeats(vehicle.seats.toString());
            setVehicleYear(vehicle.year.toString());
            setVehicleStatus(vehicle.status);
            setShowModal(true);
        }
    };

    const saveVehicle = () => {
        const plate = vehiclePlate.trim();
        const type = vehicleType.trim();
        const seats = parseInt(vehicleSeats);
        const year = parseInt(vehicleYear);
        const status = vehicleStatus;

        if (!plate || !type || !seats || !year || !status) {
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }

        if (!/^\d{2}[A-Z]-[0-9]{5}$/.test(plate)) {
            alert('Biển số xe không hợp lệ! Vui lòng nhập theo định dạng: 51B-12345');
            return;
        }

        if (seats <= 0) {
            alert('Số ghế phải lớn hơn 0!');
            return;
        }

        if (year < 1900 || year > new Date().getFullYear()) {
            alert(`Năm sản xuất phải nằm trong khoảng 1900 đến ${new Date().getFullYear()}!`);
            return;
        }

        if (editingVehicleId) {
            setVehicles(vehicles.map(v => 
                v.id === editingVehicleId 
                    ? { ...v, plate, type, seats, year, status } 
                    : v
            ));
            logAction('Sửa phương tiện', `ID: ${editingVehicleId}, Biển số: ${plate}`);
        } else {
            const newId = vehicles.length ? Math.max(...vehicles.map(v => v.id)) + 1 : 1;
            setVehicles([...vehicles, { id: newId, plate, type, seats, year, status }]);
            logAction('Thêm phương tiện', `ID: ${newId}, Biển số: ${plate}`);
        }

        setShowModal(false);
        resetForm();
    };

    const deleteVehicle = (id) => {
        const vehicle = vehicles.find(v => v.id === id);
        if (vehicle && window.confirm('Bạn có chắc muốn xóa phương tiện này?')) {
            setVehicles(vehicles.filter(v => v.id !== id));
            logAction('Xóa phương tiện', `ID: ${id}, Biển số: ${vehicle.plate}`);
        }
    };

    const logAction = (action, details) => {
        const timestamp = new Date().toLocaleString('vi-VN');
        const newLog = [...actionLog, { timestamp, action, details }];
        setActionLog(newLog);
        localStorage.setItem('actionLog', JSON.stringify(newLog));
    };

    const logout = () => {
        if (window.confirm('Bạn có chắc muốn đăng xuất?')) {
            window.location.href = 'loginadmin.html';
        }
    };

    return (
        <div className="app-container">
            {/* Sidebar */}
            <aside className="sidebar">
                <div className="logo-container">
                    <h1 className="app-logo">Phương Thanh Express</h1>
                </div>
                <nav className="nav-menu">
                    <a href="index.html" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Thống kê</span>
                    </a>
                    <a href="tickekcontroller.html" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <span>Vé xe</span>
                    </a>
                    <a href="vehicles.html" className="nav-item active">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                        <span>Phương tiện</span>
                    </a>
                    <a href="routecontroller.html" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tuyến đường</span>
                    </a>
                    <a href="#" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h2a2 2 0 012 2v5m-4 0h4"></path>
                        </svg>
                        <span>Chuyến xe</span>
                    </a>
                    <a href="#" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Thanh toán</span>
                    </a>
                    <a href="#" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Khách Hàng</span>
                    </a>
                    <a href="#" className="nav-item">
                        <svg className="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tiện Ích</span>
                    </a>
                </nav>
            </aside>

            {/* Main Content */}
            <main className="main-content">
                {/* Header */}
                <header className="header">
                    <div className="date-time">
                        {currentDate} | {currentTime}
                    </div>
                    <div className="user-controls">
                        <span className="user-email">admin@phuongthanh.com</span>
                        <button className="logout-btn" onClick={logout}>Đăng Xuất</button>
                    </div>
                </header>

                {/* Content Area */}
                <div className="content-area">
                    <div className="content-header">
                        <h2 className="content-title">Danh Sách Phương Tiện</h2>
                        <button className="add-btn" onClick={openAddVehicleModal}>
                            Thêm Phương Tiện
                        </button>
                    </div>

                    <div className="table-wrapper">
                        <table className="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Biển số xe</th>
                                    <th>Loại xe</th>
                                    <th>Số ghế</th>
                                    <th>Năm sản xuất</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                {vehicles.map(vehicle => (
                                    <tr key={vehicle.id}>
                                        <td>{vehicle.id}</td>
                                        <td>{vehicle.plate}</td>
                                        <td>{vehicle.type}</td>
                                        <td>{vehicle.seats}</td>
                                        <td>{vehicle.year}</td>
                                        <td>
                                            <span className={
                                                vehicle.status === 'Hoạt động' 
                                                    ? 'status-active' 
                                                    : vehicle.status === 'Bảo trì' 
                                                        ? 'status-maintenance' 
                                                        : 'status-inactive'
                                            }>
                                                {vehicle.status}
                                            </span>
                                        </td>
                                        <td className="action-cell">
                                            <button 
                                                className="edit-btn" 
                                                onClick={() => editVehicle(vehicle.id)}
                                            >
                                                Sửa
                                            </button>
                                            <button 
                                                className="delete-btn" 
                                                onClick={() => deleteVehicle(vehicle.id)}
                                            >
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            {/* Modal for Adding/Editing Vehicle */}
            {showModal && (
                <div className="modal-overlay">
                    <div className="modal-container">
                        <h2 className="modal-title">
                            {editingVehicleId ? 'Sửa phương tiện' : 'Thêm phương tiện'}
                        </h2>
                        <div className="modal-form">
                            <input 
                                type="text" 
                                className="form-input" 
                                placeholder="Biển số xe (VD: 51B-12345)" 
                                value={vehiclePlate}
                                onChange={(e) => setVehiclePlate(e.target.value)}
                                required 
                            />
                            <input 
                                type="text" 
                                className="form-input" 
                                placeholder="Loại xe (VD: Xe khách)" 
                                value={vehicleType}
                                onChange={(e) => setVehicleType(e.target.value)}
                                required 
                            />
                            <input 
                                type="number" 
                                className="form-input" 
                                placeholder="Số ghế" 
                                value={vehicleSeats}
                                onChange={(e) => setVehicleSeats(e.target.value)}
                                required 
                            />
                            <input 
                                type="number" 
                                className="form-input" 
                                placeholder="Năm sản xuất" 
                                value={vehicleYear}
                                onChange={(e) => setVehicleYear(e.target.value)}
                                required 
                            />
                            <select 
                                className="form-select" 
                                value={vehicleStatus}
                                onChange={(e) => setVehicleStatus(e.target.value)}
                                required
                            >
                                <option value="" disabled>Chọn trạng thái</option>
                                <option value="Hoạt động">Hoạt động</option>
                                <option value="Bảo trì">Bảo trì</option>
                                <option value="Ngừng hoạt động">Ngừng hoạt động</option>
                            </select>
                            <div className="modal-actions">
                                <button 
                                    className="cancel-btn" 
                                    onClick={() => setShowModal(false)}
                                >
                                    Hủy
                                </button>
                                <button 
                                    className="save-btn" 
                                    onClick={saveVehicle}
                                >
                                    Lưu
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Vehicles;