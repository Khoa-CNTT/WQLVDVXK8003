import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './MyBookingLogin.css';
import { useAuth } from '../../contexts/AuthContext';
import { useApi } from '../../hooks/useApi';

const MyBooking = () => {
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  const api = useApi();
  
  // Mặc định đặt isAuthenticated là false để hiển thị màn hình đăng nhập
  // Trong ứng dụng thực, bạn sẽ sử dụng useAuth() để lấy trạng thái đăng nhập
  // const [isAuthenticated, setIsAuthenticated] = useState(false);
  // const { isAuthenticated, user } = useAuth(); // Uncomment khi tích hợp với context thực

  // State variables
  const [loading, setLoading] = useState(false);
  const [bookings, setBookings] = useState([]);
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [currentBookingId, setCurrentBookingId] = useState(null);
  const [showTicketModal, setShowTicketModal] = useState(false);
  const [selectedBooking, setSelectedBooking] = useState(null);

  // Format date
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Format time
  const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('vi-VN', {
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Format currency
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0
    }).format(amount);
  };

  // Format date with day of week
  const formatDateWithDay = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  // Show notification
  const showNotification = (message, type) => {
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.classList.add("show");
    }, 10);
    
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        document.body.removeChild(notification);
      }, 300);
    }, 3000);
  };

  // Function to open cancel modal
  const handleShowCancelModal = (bookingId) => {
    setCurrentBookingId(bookingId);
    setShowCancelModal(true);
  };

  // Function to close cancel modal
  const handleCloseCancelModal = () => {
    setShowCancelModal(false);
    setCurrentBookingId(null);
  };

  // Function to open ticket modal
  const handleShowTicketModal = (booking) => {
    setSelectedBooking(booking);
    setShowTicketModal(true);
  };

  // Function to close ticket modal
  const handleCloseTicketModal = () => {
    setShowTicketModal(false);
    setSelectedBooking(null);
  };

  // Function to print ticket
  const handlePrintTicket = () => {
    window.print();
  };

  // Function to cancel booking
  const handleCancelBooking = async () => {
    if (!currentBookingId) return;
    try {
      // Gọi API hủy vé trên backend
      await api.delete(`/bookings/${currentBookingId}`);
      // Reload lại danh sách vé từ backend
      const response = await api.get('/bookings');
      setBookings(response.data.data.data || response.data.data);
      showNotification("Hủy vé thành công", "success");
      setShowCancelModal(false);
      setCurrentBookingId(null);
    } catch (error) {
      console.error("Error cancelling booking:", error);
      showNotification(`Lỗi khi hủy vé: ${error.message}`, "error");
    }
  };

  // Thêm hàm thanh toán VNPay
  const handleVnpayPayment = async (bookingId) => {
    try {
      const res = await api.post('/payments/vnpay/create', { booking_id: bookingId });
      const paymentUrl = res.data.data.payment_url;
      window.location.href = paymentUrl;
    } catch (err) {
      showNotification('Có lỗi khi tạo thanh toán VNPay', 'error');
    }
  };

  // Load user bookings
  useEffect(() => {
    if (!isAuthenticated) {
      setLoading(false);
      return;
    }
    const loadBookings = async () => {
      setLoading(true);
      try {
        const response = await api.get('/bookings');
        setBookings(response.data.data.data || response.data.data); // support for both paginated and non-paginated
      } catch (error) {
        console.error("Error loading bookings:", error);
        setBookings([]);
      } finally {
        setLoading(false);
      }
    };
    loadBookings();
  }, [isAuthenticated]);

  return (
    <div className="my-bookinglogin">
      {/* Header */}
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to="/home" className="back-link">Quay lại Trang Chủ</Link>
        </div>
      </header>
      
      <section className="container">
        <h2>VÉ ĐÃ ĐẶT</h2>
        
        {loading ? (
          <div className="loading">
            <p>Đang tải thông tin vé...</p>
          </div>
        ) : !isAuthenticated ? (
          <div className="login-required">
            <p>Vui lòng đăng nhập để xem vé đã đặt.</p>
            <div className="auth-buttons">
              <Link to="/login" className="btn-modern">Đăng nhập</Link>
              <Link to="/register" className="btn-secondary">Đăng ký</Link>
            </div>
          </div>
        ) : bookings.length === 0 ? (
          <div className="no-bookings">
            <p>Bạn chưa đặt vé nào.</p>
            <Link to="/" className="btn-modern">Đặt vé ngay</Link>
          </div>
        ) : (
          <div className="bookings-container">
            {bookings.map((booking) => {
              // Format status
              let statusClass = "";
              let statusText = "";
              
              switch(booking.status) {
                case "pending":
                  statusClass = "status-pending";
                  statusText = "Đang xử lý";
                  break;
                case "confirmed":
                  statusClass = "status-confirmed";
                  statusText = "Đã xác nhận";
                  break;
                case "completed":
                  statusClass = "status-completed";
                  statusText = "Đã hoàn thành";
                  break;
                case "cancelled":
                  statusClass = "status-cancelled";
                  statusText = "Đã hủy";
                  break;
                default:
                  statusClass = "status-default";
                  statusText = booking.status;
              }
              
              // Format payment status
              let paymentStatusClass = "";
              let paymentStatusText = "";
              
              switch(booking.payment_status) {
                case "paid":
                  paymentStatusClass = "payment-paid";
                  paymentStatusText = "Đã thanh toán";
                  break;
                case "pending":
                  paymentStatusClass = "payment-pending";
                  paymentStatusText = "Chưa thanh toán";
                  break;
                case "failed":
                  paymentStatusClass = "payment-failed";
                  paymentStatusText = "Thanh toán thất bại";
                  break;
                default:
                  paymentStatusClass = "payment-default";
                  paymentStatusText = booking.payment_status || "Chưa thanh toán";
              }
              
              return (
                <div className="booking-card" key={booking.id}>
                  <div className="booking-header">
                    <h3>
                      {booking.trip?.route?.departure || 'Đà Nẵng'} → {booking.trip?.route?.destination || 'Quảng Bình'}
                    </h3>
                    <div className="booking-statuses">
                      <span className={`status-badge ${statusClass}`}>{statusText}</span>
                      <span className={`status-badge ${paymentStatusClass}`}>{paymentStatusText}</span>
                    </div>
                  </div>
                  <div className="booking-details">
                    <div className="booking-info">
                      <p><strong>Mã đặt vé:</strong> {booking.booking_code || 'PTX' + Math.floor(Math.random() * 1000000)}</p>
                      <p><strong>Ngày đặt:</strong> {formatDate(booking.created_at || new Date())}</p>
                      <p><strong>Loại xe:</strong> {booking.trip?.vehicle?.type || 'Limousine'}</p>
                      <p><strong>Giờ khởi hành:</strong> {formatTime(booking.trip?.departure_time || new Date())}</p>
                    </div>
                    <div className="booking-info">
                      <p><strong>Số ghế:</strong> {booking.seat_count || booking.selectedSeats?.length || booking.tickets?.length || 1}</p>
                      <p><strong>Ghế:</strong> {booking.selectedSeats?.join(', ') || booking.tickets?.map(t => t.seat?.seat_number).join(', ') || '1, 2'}</p>
                      <p><strong>Giá vé:</strong> {formatCurrency(booking.total_price || 300000)}</p>
                      <p><strong>Phương thức thanh toán:</strong> {booking.payment_method === 'vnpay' ? 'VNPAY' : booking.payment_method === 'momo' ? 'MoMo' : 'Tiền mặt'}</p>
                    </div>
                  </div>
                  <div className="booking-footer">
                    <div className="passenger-info">
                      <p><strong>Hành khách:</strong> {booking.passenger_name || booking.name || 'Nguyễn Văn A'}</p>
                      <p><strong>Số điện thoại:</strong> {booking.passenger_phone || booking.phone || '0123456789'}</p>
                    </div>
                    <div className="booking-actions">
                      <button 
                        onClick={() => handleShowTicketModal(booking)} 
                        className="view-ticket-btn"
                      >
                        Xem vé
                      </button>
                      {booking.status !== 'cancelled' && booking.status !== 'completed' && (
                        <button 
                          onClick={() => handleShowCancelModal(booking.id)} 
                          className="cancel-btn"
                        >
                          Hủy vé
                        </button>
                      )}
                      {/* Nút thanh toán VNPay */}
                      {booking.payment_status !== 'paid' && booking.status !== 'cancelled' && (
                        <button 
                          onClick={() => handleVnpayPayment(booking.id)} 
                          className="pay-btn"
                        >
                          Thanh toán VNPay
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </section>

      {/* Confirm cancel modal */}
      {showCancelModal && (
        <div className="cancel-modal">
          <div className="modal-overlay"></div>
          <div className="modal-content">
            <h3>Xác nhận hủy vé</h3>
            <p>Bạn có chắc chắn muốn hủy vé này không? Hành động này không thể hoàn tác.</p>
            <div className="modal-actions">
              <button onClick={handleCloseCancelModal} className="btn-secondary">Không, giữ lại</button>
              <button onClick={handleCancelBooking} className="btn-cancel">Có, hủy vé</button>
            </div>
          </div>
        </div>
      )}

      {/* Ticket detail modal */}
      {showTicketModal && selectedBooking && (
        <div className="ticket-modal">
          <div className="modal-overlay" onClick={handleCloseTicketModal}></div>
          <div className="ticket-wrapper">
            <button onClick={handleCloseTicketModal} className="close-modal">×</button>
            <div className="ticket-container">
              <div className="ticket">
                <div className="ticket-header">
                  <div className="company-logo">
                    <h2>PHƯƠNG THANH EXPRESS</h2>
                  </div>
                  <div className="ticket-title">
                    <h3>VÉ XE KHÁCH</h3>
                    <p>E-TICKET</p>
                  </div>
                </div>
                
                <div className="ticket-body">
                  <div className="ticket-route">
                    <div className="route-from">
                      <h4>{selectedBooking.trip?.route?.departure || 'Đà Nẵng'}</h4>
                    </div>
                    <div className="route-arrow">
                      <span>→</span>
                    </div>
                    <div className="route-to">
                      <h4>{selectedBooking.trip?.route?.destination || 'Quảng Bình'}</h4>
                    </div>
                  </div>

                  <div className="ticket-qr">
                    <div className="qr-placeholder">
                      <p>{selectedBooking.booking_code}</p>
                    </div>
                  </div>

                  <div className="ticket-detaillogin">
                    <div className="ticket-info-row">
                      <div className="ticket-info-item">
                        <label>Mã đặt vé</label>
                        <p>{selectedBooking.booking_code}</p>
                      </div>
                      <div className="ticket-info-item">
                        <label>Ngày đặt</label>
                        <p>{formatDate(selectedBooking.created_at)}</p>
                      </div>
                    </div>

                    <div className="ticket-info-row">
                      <div className="ticket-info-item">
                        <label>Ngày khởi hành</label>
                        <p>{formatDateWithDay(selectedBooking.trip?.departure_time)}</p>
                      </div>
                      <div className="ticket-info-item">
                        <label>Giờ khởi hành</label>
                        <p>{formatTime(selectedBooking.trip?.departure_time)}</p>
                      </div>
                    </div>

                    <div className="ticket-info-row">
                      <div className="ticket-info-item">
                        <label>Loại xe</label>
                        <p>{selectedBooking.trip?.vehicle?.type}</p>
                      </div>
                      <div className="ticket-info-item">
                        <label>Số ghế</label>
                        <p>{selectedBooking.selectedSeats?.join(', ')}</p>
                      </div>
                    </div>

                    <div className="ticket-info-row">
                      <div className="ticket-info-item">
                        <label>Hành khách</label>
                        <p>{selectedBooking.passenger_name}</p>
                      </div>
                      <div className="ticket-info-item">
                        <label>Số điện thoại</label>
                        <p>{selectedBooking.passenger_phone}</p>
                      </div>
                    </div>

                    <div className="ticket-info-row">
                      <div className="ticket-info-item full-width">
                        <label>Tổng tiền</label>
                        <p className="ticket-price">{formatCurrency(selectedBooking.total_price)}</p>
                      </div>
                    </div>
                  </div>

                  <div className="ticket-notice">
                    <p>Vui lòng mang theo CMND/CCCD khi lên xe. Có mặt tại bến 30 phút trước giờ khởi hành.</p>
                    <p>Hotline hỗ trợ: 0905.999999</p>
                  </div>
                </div>
                
                <div className="ticket-footer">
                  <div className="ticket-status">
                    <span className={selectedBooking.status === 'cancelled' ? 'cancelled' : (selectedBooking.payment_status === 'paid' ? 'paid' : 'pending')}>
                      {selectedBooking.status === 'cancelled' ? 'ĐÃ HỦY' : (selectedBooking.payment_status === 'paid' ? 'ĐÃ THANH TOÁN' : 'CHƯA THANH TOÁN')}
                    </span>
                  </div>
                  <div className="company-info">
                    <p>Phương Thanh Express - Bình An Trên Vạn Dặm</p>
                  </div>
                </div>
              </div>
            </div>
            <div className="ticket-actions">
              <button onClick={handlePrintTicket} className="btn-print">In vé</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MyBooking;