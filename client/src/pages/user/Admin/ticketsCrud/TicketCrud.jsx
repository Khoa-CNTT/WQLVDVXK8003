import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './TicketCrud.css';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import { Storage } from '../../../../constant/storage';

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

    // State cho phân trang
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const totalPages = Math.ceil(tickets.length / itemsPerPage);
    const paginatedTickets = tickets.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

    // Hàm lấy token từ localStorage
    const getToken = () => {
        const authData = localStorage.getItem(Storage.AUTH_DATA);
        console.log('authData',authData)
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
                        axios.get('http://127.0.0.1:8000/api/v1/admin/lines', config),
                        axios.get('http://127.0.0.1:8000/api/v1/admin/tickets/all', config)
                    ]);

                    // Xử lý dữ liệu tuyến đường
                    if (routesResponse.data && routesResponse.data.data) {
                        setRoutes(routesResponse.data.data);
                    }

                    // Xử lý dữ liệu vé xe
                    if (ticketsResponse.data && ticketsResponse.data.data) {
                        // Nếu trả về dạng phân trang Laravel thì lấy .data, còn không thì lấy trực tiếp
                        const ticketList = Array.isArray(ticketsResponse.data.data)
                          ? ticketsResponse.data.data
                          : ticketsResponse.data.data.data || [];
                        setTickets(ticketList);
                    }

                    setLoading(false);
                } catch (apiError) {
                    console.error('API error:', apiError);
                    setLoading(false);
                }
            } catch (err) {
                console.error('Error fetching data:', err);
                setError(err.message || 'Đã xảy ra lỗi khi tải dữ liệu');
                setLoading(false);
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
        <HomeAdminLayout>
            <div className="app-container">
                {/* Nội dung chính */}
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
                                {paginatedTickets?.map(ticket => (
                                    <tr key={ticket.id}>
                                        <td>{ticket.id}</td>
                                        <td>{ticket.trip?.line ? `${ticket.trip.line.departure} → ${ticket.trip.line.destination}` : 'Không xác định'}</td>
                                        <td>{ticket.booking?.passenger_name || ticket.booking?.user?.name || 'N/A'}</td>
                                        <td>{ticket.trip?.departure_time ? formatDate(ticket.trip.departure_time) : 'N/A'}</td>
                                        <td>{ticket.seat?.seat_number || 'N/A'}</td>
                                        <td>{(Number(ticket.trip?.price) || 0).toLocaleString('vi-VN')} VNĐ</td>
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
                                            <button className="edit-btn" onClick={() => editTicket(ticket.id)}>Sửa</button>
                                            <button className="delete-btn" onClick={() => deleteTicket(ticket.id)}>Xóa</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}

                    {/* Phân trang dưới bảng */}
                    <div className="pagination">
                        <button className="pagination-btn" onClick={() => setCurrentPage(p => Math.max(1, p - 1))} disabled={currentPage === 1}>
                            &#8592;
                        </button>
                        {Array.from({ length: totalPages }, (_, i) => (
                            <button
                                key={i + 1}
                                className={`pagination-btn${currentPage === i + 1 ? ' active' : ''}`}
                                onClick={() => setCurrentPage(i + 1)}
                                style={{ minWidth: 40 }}
                            >
                                {i + 1}
                            </button>
                        ))}
                        <button className="pagination-btn" onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))} disabled={currentPage === totalPages}>
                            &#8594;
                        </button>
                    </div>
                </div>
                {/* Modal cho Thêm/Sửa vé xe */}
                <ReusableModal
                    show={showModal}
                    title={modalTitle}
                    onClose={() => setShowModal(false)}
                    onSubmit={saveTicket}
                >
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
                </ReusableModal>

            </div>
        </HomeAdminLayout>

    );
};

export default TicketCrud;