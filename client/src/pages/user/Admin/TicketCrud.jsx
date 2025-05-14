import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import './TicketCrud.css';

const TicketCrud = () => {
    const [currentDateTime, setCurrentDateTime] = useState('');
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    
    // State cho modal
    const [showModal, setShowModal] = useState(false);
    const [modalTitle, setModalTitle] = useState('Thêm vé xe');
    
    // States cho dữ liệu
    const [tickets, setTickets] = useState([]);
    const [routes, setRoutes] = useState([]);
    const [editingTicketId, setEditingTicketId] = useState(null);
    
    // States cho form
    const [formData, setFormData] = useState({
        routeId: '',
        customer: '',
        seat: '',
        status: '',
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
    
    // Lấy dữ liệu từ API
    useEffect(() => {
        const fetchData = async () => {
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
                
                try {
                    // Lấy dữ liệu tuyến đường và vé xe
                    const [routesResponse, ticketsResponse] = await Promise.all([
                        axios.get('http://127.0.0.1:8000/api/v1/admin/routes', config),
                        axios.get('http://127.0.0.1:8000/api/v1/admin/tickets/all', config)
                    ]);
                    
                    // Xử lý dữ liệu tuyến đường
                    if (routesResponse.data && routesResponse.data.data) {
                        setRoutes(routesResponse.data.data);
                    }
                    
                    // Xử lý dữ liệu vé xe
                    if (ticketsResponse.data && ticketsResponse.data.data) {
                        setTickets(ticketsResponse.data.data);
                    }
                    
                    setLoading(false);
                } catch (apiError) {
                    console.error('API error:', apiError);
                    // Sử dụng dữ liệu mẫu nếu API lỗi
                    setRoutes([
                        { id: 1, name: "Đà Nẵng - Hà Giang", seats: 45, departure_date: "2025-05-01", price: 800000 },
                        { id: 2, name: "Đà Nẵng - Quảng Bình", seats: 40, departure_date: "2025-05-02", price: 500000 },
                        { id: 3, name: "Đà Nẵng - Nghệ An", seats: 50, departure_date: "2025-05-03", price: 600000 }
                    ]);
                    
                    setTickets([
                        { id: 101, route_id: 1, customer_name: "Nguyễn Văn A", seat_number: 5, total_price: 800000, status: "completed" },
                        { id: 102, route_id: 1, customer_name: "Trần Thị B", seat_number: 12, total_price: 800000, status: "pending" },
                        { id: 103, route_id: 2, customer_name: "Lê Văn C", seat_number: 8, total_price: 500000, status: "completed" }
                    ]);
                    
                    setLoading(false);
                }
            } catch (err) {
                console.error('Error fetching data:', err);
                setError(err.message || 'Đã xảy ra lỗi khi tải dữ liệu');
                setLoading(false);
                
                // Sử dụng dữ liệu mẫu trong trường hợp lỗi
                setRoutes([
                    { id: 1, name: "Đà Nẵng - Hà Giang", seats: 45, departure_date: "2025-05-01", price: 800000 },
                    { id: 2, name: "Đà Nẵng - Quảng Bình", seats: 40, departure_date: "2025-05-02", price: 500000 },
                    { id: 3, name: "Đà Nẵng - Nghệ An", seats: 50, departure_date: "2025-05-03", price: 600000 }
                ]);
                
                setTickets([
                    { id: 101, route_id: 1, customer_name: "Nguyễn Văn A", seat_number: 5, total_price: 800000, status: "completed" },
                    { id: 102, route_id: 1, customer_name: "Trần Thị B", seat_number: 12, total_price: 800000, status: "pending" },
                    { id: 103, route_id: 2, customer_name: "Lê Văn C", seat_number: 8, total_price: 500000, status: "completed" }
                ]);
            }
        };
        
        fetchData();
    }, []);
    
    // Mở modal thêm vé
    const openAddTicketModal = () => {
        setEditingTicketId(null);
        setModalTitle('Thêm vé xe');
        setFormData({
            routeId: '',
            customer: '',
            seat: '',
            status: ''
        });
        setShowModal(true);
    };
    
    // Mở modal sửa vé
    const editTicket = (id) => {
        const ticket = tickets.find(t => t.id === id);
        if (ticket) {
            setEditingTicketId(id);
            setModalTitle('Sửa vé xe');
            setFormData({
                routeId: ticket.route_id,
                customer: ticket.customer_name,
                seat: ticket.seat_number,
                status: ticket.status
            });
            setShowModal(true);
        }
    };
    
    // Xử lý thay đổi form
    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData({
            ...formData,
            [name]: value
        });
    };
    
    // Lưu vé
    const saveTicket = async () => {
        const { routeId, customer, seat, status } = formData;
        
        if (!routeId || !customer || !seat || !status) {
            alert('Vui lòng điền đầy đủ thông tin!');
            return;
        }
        
        const route = routes.find(r => r.id === parseInt(routeId));
        if (!route) {
            alert('Tuyến đường không tồn tại!');
            return;
        }
        
        if (parseInt(seat) <= 0 || parseInt(seat) > route.seats) {
            alert(`Số ghế phải từ 1 đến ${route.seats}!`);
            return;
        }
        
        try {
            const token = getToken();
            const config = {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            };
            
            const ticketData = {
                route_id: parseInt(routeId),
                customer_name: customer,
                seat_number: parseInt(seat),
                status: status,
                total_price: route.price
            };
            
            if (editingTicketId) {
                // Cập nhật vé
                await axios.put(`http://127.0.0.1:8000/api/v1/admin/tickets/${editingTicketId}`, ticketData, config);
                
                // Cập nhật state
                setTickets(tickets.map(ticket => 
                    ticket.id === editingTicketId 
                    ? { 
                        ...ticket, 
                        route_id: parseInt(routeId),
                        customer_name: customer,
                        seat_number: parseInt(seat),
                        status: status,
                        total_price: route.price
                    } 
                    : ticket
                ));
            } else {
                // Thêm vé mới
                const response = await axios.post('http://127.0.0.1:8000/api/v1/admin/tickets', ticketData, config);
                
                if (response.data && response.data.data) {
                    // Thêm vé mới vào state
                    setTickets([...tickets, response.data.data]);
                }
            }
            
            // Đóng modal
            setShowModal(false);
        } catch (error) {
            console.error('Error saving ticket:', error);
            alert('Đã xảy ra lỗi khi lưu vé xe!');
            
            // Fallback nếu API lỗi
            if (editingTicketId) {
                // Cập nhật state cho trường hợp sửa
                setTickets(tickets.map(ticket => 
                    ticket.id === editingTicketId 
                    ? { 
                        ...ticket, 
                        route_id: parseInt(routeId),
                        customer_name: customer,
                        seat_number: parseInt(seat),
                        status: status,
                        total_price: route.price
                    } 
                    : ticket
                ));
            } else {
                // Tạo ID mới và thêm vào state cho trường hợp thêm mới
                const newId = tickets.length ? Math.max(...tickets.map(t => t.id)) + 1 : 101;
                const newTicket = {
                    id: newId,
                    route_id: parseInt(routeId),
                    customer_name: customer,
                    seat_number: parseInt(seat),
                    status: status,
                    total_price: route.price
                };
                setTickets([...tickets, newTicket]);
            }
            
            // Đóng modal
            setShowModal(false);
        }
    };
    
    // Xóa vé
    const deleteTicket = async (id) => {
        if (window.confirm('Bạn có chắc muốn xóa vé xe này?')) {
            try {
                const token = getToken();
                const config = {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                };
                
                // Gọi API xóa vé
                await axios.delete(`http://127.0.0.1:8000/api/v1/admin/tickets/${id}`, config);
                
                // Cập nhật state
                setTickets(tickets.filter(ticket => ticket.id !== id));
            } catch (error) {
                console.error('Error deleting ticket:', error);
                alert('Đã xảy ra lỗi khi xóa vé xe!');
                
                // Fallback nếu API lỗi
                setTickets(tickets.filter(ticket => ticket.id !== id));
            }
        }
    };
    
    // Hàm đăng xuất
    const handleLogout = () => {
        if (window.confirm('Bạn có chắc muốn đăng xuất?')) {
            localStorage.removeItem('authData');
            localStorage.removeItem('userInfo');
            window.location.href = '/login';
        }
    };
    
    // Chuyển đổi trạng thái vé thành tiếng Việt
    const translateStatus = (status) => {
        const statusMap = {
            'completed': 'Thành công',
            'pending': 'Đang xử lý',
            'canceled': 'Hủy'
        };
        return statusMap[status] || status;
    };
    
    // Hàm định dạng ngày tháng
    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', { year: 'numeric', month: '2-digit', day: '2-digit' }).replace(/\//g, '-');
    };

    return (
        <div className="app-container">
            {/* Sidebar */}
            <aside className="sidebar">
                <div className="company-name">
                    <h1>Phương Thanh Express</h1>
                </div>
                <nav className="menu">
                    <Link to="/dashboard" className="menu-item">
                        <svg className="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Thống kê</span>
                    </Link>
                    <Link to="/tickets" className="menu-item active">
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
                <header className="top-header">
                    <div className="date-time">{currentDateTime}</div>
                    <div className="user-section">
                        <span className="user-email">admin@phuongthanh.com</span>
                        <button className="logout-btn" onClick={handleLogout}>Đăng Xuất</button>
                    </div>
                </header>

                {/* Danh sách vé xe */}
                <div className="ticket-container">
                    <h1 className="page-title">Danh Sách Vé Xe</h1>
                    
                    <div className="action-bar">
                        <button className="add-btn" onClick={openAddTicketModal}>Thêm Vé Xe</button>
                    </div>

                    {loading ? (
                        <div className="loading-indicator">Đang tải dữ liệu...</div>
                    ) : error ? (
                        <div className="error-message">
                            <p>{error}</p>
                        </div>
                    ) : (
                        <table className="ticket-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tuyến đường</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày đi</th>
                                    <th>Số ghế</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                {tickets.map(ticket => {
                                    const route = routes.find(r => r.id === ticket.route_id);
                                    return (
                                        <tr key={ticket.id}>
                                            <td>{ticket.id}</td>
                                            <td>{route ? route.name : 'Không xác định'}</td>
                                            <td>{ticket.customer_name}</td>
                                            <td>{route ? formatDate(route.departure_date) : 'N/A'}</td>
                                            <td>{ticket.seat_number}</td>
                                            <td>{ticket.total_price.toLocaleString('vi-VN')} VNĐ</td>
                                            <td>
                                                <span className={
                                                    ticket.status === 'completed' ? 'status-success' : 
                                                    ticket.status === 'pending' ? 'status-pending' : 
                                                    'status-canceled'
                                                }>
                                                    {translateStatus(ticket.status)}
                                                </span>
                                            </td>
                                            <td className="action-buttons">
                                                <button 
                                                    className="edit-btn" 
                                                    onClick={() => editTicket(ticket.id)}
                                                >
                                                    Sửa
                                                </button>
                                                <button 
                                                    className="delete-btn" 
                                                    onClick={() => deleteTicket(ticket.id)}
                                                >
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    )}
                </div>
            </main>

            {/* Modal cho Thêm/Sửa vé xe */}
            {showModal && (
                <div className="modal">
                    <div className="modal-content">
                        <h2 className="modal-title">{modalTitle}</h2>
                        <div className="form-container">
                            <select 
                                name="routeId"
                                value={formData.routeId} 
                                onChange={handleInputChange}
                                className="form-input" 
                                required
                            >
                                <option value="" disabled>Chọn tuyến đường</option>
                                {routes.map(route => (
                                    <option key={route.id} value={route.id}>
                                        {route.name} ({formatDate(route.departure_date)})
                                    </option>
                                ))}
                            </select>
                            
                            <input 
                                type="text" 
                                name="customer"
                                value={formData.customer} 
                                onChange={handleInputChange}
                                className="form-input" 
                                placeholder="Tên khách hàng" 
                                required
                            />
                            
                            <input 
                                type="number" 
                                name="seat"
                                value={formData.seat} 
                                onChange={handleInputChange}
                                className="form-input" 
                                placeholder="Số ghế" 
                                required
                            />
                            
                            <select 
                                name="status"
                                value={formData.status} 
                                onChange={handleInputChange}
                                className="form-input" 
                                required
                            >
                                <option value="" disabled>Chọn trạng thái</option>
                                <option value="completed">Thành công</option>
                                <option value="pending">Đang xử lý</option>
                                <option value="canceled">Hủy</option>
                            </select>
                            
                            <div className="modal-actions">
                                <button 
                                    onClick={() => setShowModal(false)} 
                                    className="cancel-btn"
                                >
                                    Hủy
                                </button>
                                <button 
                                    onClick={saveTicket} 
                                    className="save-btn"
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

export default TicketCrud;