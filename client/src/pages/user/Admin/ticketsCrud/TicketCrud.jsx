import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './TicketCrud.css';
import HomeAdminLayout from '../../../../layouts/AdminLayout';
import ReusableModal from '../../../../components/ReusableModal/ReusableModal';
import { Storage } from '../../../../constant/storage';
import { useForm } from 'react-hook-form';
import { toast } from 'react-toastify';
import { useApi } from '../../../../hooks/useApi';
import { confirmAction, fetchSortedData } from '../../../../utils';

const TicketCrud = () => {
    const api = useApi();
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

    // State cho phân trang
    const [currentPage, setCurrentPage] = useState(1);
    const itemsPerPage = 10;
    const totalPages = Math.ceil(tickets.length / itemsPerPage);
    const paginatedTickets = tickets.slice((currentPage - 1) * itemsPerPage, currentPage * itemsPerPage);

    const {
        register,
        handleSubmit,
        reset,
        formState: { errors },
    } = useForm({
        mode: 'onChange',
        defaultValues: {
            trip_id: '',
            user_id: '',
            seat_number: '',
            status: '',
        },
    });

    // Hàm lấy token từ localStorage
    const getToken = () => {
        const authData = localStorage.getItem(Storage.AUTH_DATA);
        console.log('authData', authData)
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
                        console.log('ticketList', ticketList)
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


    // Mở modal sửa vé
    const editTicket = (ticket) => {
        const ticketData = {
            trip_id: ticket.trip_id,
            user_id: ticket?.booking.user_id,
            seat_number: ticket?.seat.seat_number,
            status: ticket.status
        };
        console.log('ticketData', ticketData);

        // Cập nhật giá trị mặc định trong form react-hook-form
        reset(ticketData);

        // Mở modal và cập nhật tiêu đề
        setModalTitle('Chỉnh sửa vé');
        setEditingTicketId(ticket.id);
        setShowModal(true);
    };



    // Lưu vé
    const saveTicket = async (data) => {
        try {
            const payload = { ...data }
            await api.put(`/admin/tickets/${editingTicketId}/status`, payload);
            toast.success('Cập nhật người vé thành công!');


        } catch (error) {
            console.error(error);
            toast.error('Lỗi khi lưu vé');
        }
        setShowModal(false);
        setEditingTicketId(null)
        const updatedTickets = await fetchSortedData(api, '/admin/tickets/all');
        setTickets(updatedTickets)
    };

    // Xóa vé
    const deleteTicket = (id) => {
        confirmAction({
            title: 'Xác nhận xóa vé xe',
            text: `Bạn có chắc muốn xóa vé xe ID ${id}?`,
            onConfirm: async () => {
                try {
                    await api.delete(`/admin/tickets/${id}`);
                    toast.success('Xóa vé xe thành công');
                    const updatedTickets = await fetchSortedData(api, '/admin/tickets/all');
                    setTickets(updatedTickets)

                } catch (error) {
                    toast.error('Lỗi khi xóa vé xe');
                }
            },
        });
    };

    // Chuyển đổi trạng thái vé thành tiếng Việt
    const translateStatus = (status) => {
        const statusMap = {
            'completed': 'Đã thanh toán',
            'pending': 'Chưa thanh toán',
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
                                            <button className="edit-btn" onClick={() => editTicket(ticket)}>Sửa</button>
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
                    onSubmit={handleSubmit(saveTicket)}

                >
                    <div className="form-group">
                        <label>Trạng thái:</label>
                        <select
                            className="form-input"
                            {...register('status')}
                        >
                            <option value="" disabled>Chọn trạng thái</option>
                            <option value="completed">Đã thanh toán</option>
                            <option value="pending">Chưa thanh toán</option>
                            <option value="canceled">Hủy</option>
                        </select>
                        {errors.status && <p className="!text-red-500 !text-sm !mb-0 !mt-0.5">{errors.role_id.message}</p>}
                    </div>

                </ReusableModal>

            </div>
        </HomeAdminLayout>

    );
};

export default TicketCrud;