import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import './Ticket_Detail.css';

const Ticket_Detail = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();
  
  // State variables
  const [loading, setLoading] = useState(true);
  const [tripSeats, setTripSeats] = useState([]);
  const [selectedSeats, setSelectedSeats] = useState([]);
  const [passengerName, setPassengerName] = useState('');
  const [passengerPhone, setPassengerPhone] = useState('');
  const [passengerEmail, setPassengerEmail] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('cash');
  const [bookingResult, setBookingResult] = useState({ message: '', isError: false });
  const [showSuccessModal, setShowSuccessModal] = useState(false);
  const [bookingCode, setBookingCode] = useState('');
  const [submitting, setSubmitting] = useState(false);

  // Get URL query parameters
  const queryParams = new URLSearchParams(location.search);
  const tripId = queryParams.get('tripId');
  const busName = queryParams.get('busName');
  const busTime = queryParams.get('busTime');
  const busPrice = parseInt(queryParams.get('busPrice'));

  // Format currency
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0
    }).format(amount);
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

  // Handle go to home
  const handleGoHome = () => {
    setShowSuccessModal(false);
    navigate('/');
  };

  // Handle view bookings
  const handleViewBookings = () => {
    setShowSuccessModal(false);
    navigate('/my-bookings');
  };

  // Go to login page with redirect back to current page
  const handleGoToLogin = () => {
    navigate(`/login?redirect=${encodeURIComponent(location.pathname + location.search)}`);
  };

  // Load seat data
  useEffect(() => {
    if (!tripId || !busName || !busTime || !busPrice) {
      setLoading(false);
      return;
    }

    // Fill in passenger info if user is logged in
    if (isAuthenticated && user) {
      setPassengerName(user.name || '');
      setPassengerPhone(user.phone || '');
      setPassengerEmail(user.email || '');
    }

    const loadSeats = async () => {
      try {
        // In a real application, you would fetch the seats from your API
        // const response = await fetch(`your-api-url/trips/${tripId}/seats`);
        // const data = await response.json();
        // setTripSeats(data.seats);
        
        // For now, we'll use sample data
        setTripSeats(generateSampleSeats());
        setLoading(false);
      } catch (error) {
        console.error("Error loading seat data:", error);
        setTripSeats(generateSampleSeats());
        setLoading(false);
      }
    };

    loadSeats();
  }, [tripId, busName, busTime, busPrice, isAuthenticated, user]);

  // Generate sample seats for testing
  const generateSampleSeats = () => {
    const sampleSeats = [];
    const totalSeats = 20;
    const takenSeats = [2, 5, 8, 12, 15]; // Sample taken seats
    
    for (let i = 1; i <= totalSeats; i++) {
      sampleSeats.push({
        id: i,
        seat_number: i,
        status: takenSeats.includes(i) ? 'taken' : 'available',
        booking_status: takenSeats.includes(i) ? 'booked' : 'available'
      });
    }
    
    return sampleSeats;
  };

  // Toggle seat selection
  const toggleSeat = (seatId) => {
    // Nếu chưa đăng nhập, hiển thị thông báo và không cho chọn ghế
    if (!isAuthenticated) {
      showNotification("Vui lòng đăng nhập để chọn ghế!", "error");
      return;
    }
    
    setSelectedSeats(prevSelectedSeats => {
      if (prevSelectedSeats.includes(seatId)) {
        return prevSelectedSeats.filter(id => id !== seatId);
      } else {
        return [...prevSelectedSeats, seatId];
      }
    });
  };

  // Calculate total price
  const totalPrice = selectedSeats.length * busPrice;

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
  
    if (!isAuthenticated) {
      showNotification("Vui lòng đăng nhập để đặt vé!", "error");
      handleGoToLogin();
      return;
    }
  
    if (!passengerName || !passengerPhone || !passengerEmail) {
      setBookingResult({
        message: "Vui lòng điền đầy đủ thông tin!",
        isError: true,
      });
      return;
    }
  
    if (selectedSeats.length === 0) {
      setBookingResult({
        message: "Vui lòng chọn ít nhất một ghế!",
        isError: true,
      });
      return;
    }
  
    setSubmitting(true);
  
    try {
      // Gửi yêu cầu tạo booking
      const bookingData = {
        trip_id: tripId,
        seat_ids: selectedSeats,
        passenger_name: passengerName,
        passenger_phone: passengerPhone,
        passenger_email: passengerEmail,
        payment_method: paymentMethod,
      };
  
      const bookingResponse = await fetch('http://localhost:8000/api/bookings', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`, // Đảm bảo token hợp lệ
        },
        body: JSON.stringify(bookingData),
      });
  
      const bookingDataResponse = await bookingResponse.json();
  
      if (!bookingDataResponse.success) {
        throw new Error(bookingDataResponse.message || 'Đặt vé thất bại');
      }
  
      const bookingId = bookingDataResponse.booking.id;
  
      // Gửi yêu cầu thanh toán VNPay
      if (paymentMethod === "vnpay") {
        const paymentResponse = await fetch('http://localhost:8000/api/v1/payments/vnpay', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`, // Đảm bảo token khớp với Postman
          },
          body: JSON.stringify({
            booking_id: bookingId,
          }),
        });
  
        const paymentData = await paymentResponse.json();
  
        if (paymentData.success) {
          window.location.href = paymentData.data.payment_url;
          return;
        } else {
          throw new Error(paymentData.message || 'Không thể tạo thanh toán VNPay');
        }
      }
  
      // Xử lý các phương thức thanh toán khác hoặc tiền mặt
      setBookingCode(bookingDataResponse.booking.booking_code);
      setShowSuccessModal(true);
    } catch (error) {
      console.error('Lỗi đặt vé:', error);
      setBookingResult({
        message: `Đặt vé thất bại: ${error.message}`,
        isError: true,
      });
    } finally {
      setSubmitting(false);
    }
  };

  // If trip data is missing, show error
  if (!tripId || !busName || !busTime || !busPrice) {
    return (
      <div className="ticket-detail">
        <header>
          <div className="container">
            <h1>Phương Thanh Express</h1>
            <Link to="/booking-results" className="back-link">Quay lại Tìm kiếm</Link>
          </div>
        </header>

        <section className="container">
          <h2>CHI TIẾT ĐẶT VÉ</h2>
          <div className="my-8 text-center text-gray-600">
            <p>Không có thông tin vé. Vui lòng quay lại trang tìm kiếm.</p>
          </div>
        </section>
      </div>
    );
  }

  return (
    <div className="ticket-detail">
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to="/booking-results" className="back-link">Quay lại Tìm kiếm</Link>
        </div>
      </header>

      <section className="container">
        <h2>CHI TIẾT ĐẶT VÉ</h2>
        
        {loading ? (
          <div className="loading">
            <p>Đang tải thông tin chuyến xe...</p>
          </div>
        ) : (
          <div className="booking-content">
            {/* Thông báo đăng nhập nổi bật cho người chưa đăng nhập */}
            {!isAuthenticated && (
              <div className="login-alert">
                <h3>Bạn cần đăng nhập để đặt vé</h3>
                <p>Vui lòng đăng nhập để có thể chọn ghế và đặt vé cho chuyến đi này.</p>
                <div className="alert-actions">
                  <button onClick={handleGoToLogin} className="btn-modern">
                    Đăng nhập ngay
                  </button>
                  <Link to="/register" className="btn-secondary">
                    Đăng ký tài khoản
                  </Link>
                </div>
              </div>
            )}

            <h3>Thông tin chuyến xe</h3>
            <p className="bus-info">Chuyến xe: {busName} | Giờ khởi hành: {busTime} | Giá vé: {formatCurrency(busPrice)}</p>
            <p className="total-price">Tổng tiền: {formatCurrency(totalPrice)}</p>

            {/* Sơ đồ chọn ghế */}
            <h3>Chọn ghế ngồi</h3>
            <div className={`seat-map ${!isAuthenticated ? 'disabled-seats' : ''}`}>
              {tripSeats.map((seat) => (
                <div 
                  key={seat.id}
                  className={`seat ${
                    seat.status === 'taken' || seat.booking_status === 'booked' 
                      ? 'taken' 
                      : selectedSeats.includes(seat.id) 
                        ? 'selected' 
                        : 'available'
                  } ${!isAuthenticated ? 'no-click' : ''}`}
                  onClick={() => 
                    seat.status !== 'taken' && seat.booking_status !== 'booked' && 
                    toggleSeat(seat.id)
                  }
                >
                  {seat.seat_number}
                </div>
              ))}
            </div>
            
            <div className="seat-legend">
              <div className="legend-item">
                <div className="seat-example available"></div>
                <span>Ghế trống</span>
              </div>
              <div className="legend-item">
                <div className="seat-example selected"></div>
                <span>Ghế đã chọn</span>
              </div>
              <div className="legend-item">
                <div className="seat-example taken"></div>
                <span>Ghế đã đặt</span>
              </div>
            </div>
            
            <p className="selected-seats">
              Ghế đã chọn: <span>{selectedSeats.length > 0 ? selectedSeats.join(', ') : 'Chưa chọn'}</span> ({selectedSeats.length})
            </p>

            <h3>Thông tin hành khách</h3>
            <form onSubmit={handleSubmit}>
              <div className="form-group">
                <label htmlFor="passengerName">Họ và tên:</label>
                <input 
                  type="text" 
                  id="passengerName" 
                  value={passengerName}
                  onChange={(e) => setPassengerName(e.target.value)}
                  required 
                  placeholder="Nhập họ và tên"
                  disabled={!isAuthenticated}
                  className={!isAuthenticated ? 'input-disabled' : ''}
                />
              </div>
              <div className="form-group">
                <label htmlFor="passengerPhone">Số điện thoại:</label>
                <input 
                  type="tel" 
                  id="passengerPhone" 
                  value={passengerPhone}
                  onChange={(e) => setPassengerPhone(e.target.value)}
                  required 
                  placeholder="Nhập số điện thoại"
                  disabled={!isAuthenticated}
                  className={!isAuthenticated ? 'input-disabled' : ''}
                />
              </div>
              <div className="form-group">
                <label htmlFor="passengerEmail">Email:</label>
                <input 
                  type="email" 
                  id="passengerEmail" 
                  value={passengerEmail}
                  onChange={(e) => setPassengerEmail(e.target.value)}
                  required 
                  placeholder="Nhập email"
                  disabled={!isAuthenticated}
                  className={!isAuthenticated ? 'input-disabled' : ''}
                />
              </div>
              <div className="form-group">
                <label htmlFor="paymentMethod">Phương thức thanh toán:</label>
                <select 
                  id="paymentMethod"
                  value={paymentMethod}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                  disabled={!isAuthenticated}
                  className={!isAuthenticated ? 'input-disabled' : ''}
                >
                  <option value="cash">Thanh toán khi lên xe</option>
                  <option value="vnpay">VNPAY</option>
                  <option value="momo">MOMO</option>
                </select>
              </div>
              <button 
                type="submit" 
                className={`btn-modern ${(submitting || !isAuthenticated) ? 'disabled' : ''}`}
                disabled={submitting || !isAuthenticated}
              >
                {!isAuthenticated 
                  ? 'Đăng nhập để đặt vé' 
                  : (submitting ? 'Đang xử lý...' : 'Xác nhận đặt vé')
                }
              </button>
            </form>
            
            {!isAuthenticated && (
              <div className="login-reminder">
                <p>
                  Bạn cần <button onClick={handleGoToLogin} className="login-link">đăng nhập</button> để đặt vé. 
                  Chưa có tài khoản? <Link to="/register" className="register-link">Đăng ký ngay</Link>
                </p>
              </div>
            )}
            
            {bookingResult.message && (
              <div className={`booking-result ${bookingResult.isError ? 'error' : 'success'}`}>
                {bookingResult.message}
              </div>
            )}
          </div>
        )}
      </section>

      {/* Thông báo thành công */}
      {showSuccessModal && (
        <div className="success-modal">
          <div className="modal-overlay"></div>
          <div className="modal-content">
            <div className="modal-body">
              <svg className="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
              </svg>
              <h3>Đặt vé thành công!</h3>
              <p>Cảm ơn bạn đã đặt vé tại Phương Thanh Express.</p>
              <p className="booking-code">Mã đặt vé: {bookingCode}</p>
              <div className="modal-actions">
                {/* Sử dụng buttons với handlers thay vì Link component */}
                <button onClick={handleGoHome} className="btn-modern">Về trang chủ</button>
                <button onClick={handleViewBookings} className="btn-secondary">Xem vé đã đặt</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default Ticket_Detail;