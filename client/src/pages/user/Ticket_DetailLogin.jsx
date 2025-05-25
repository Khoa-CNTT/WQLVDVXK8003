import React, { useState, useEffect } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import './Ticket_DetailLogin.css';
import { useApi } from '../../hooks/useApi';
import { formatTime } from '../../utils';

const Ticket_Detail = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { isAuthenticated, user } = useAuth();

  const { id } = useParams();
  const [tripData, setTripData] = useState([]);
  const [seatData, setSeatData] = useState([])
  const api = useApi()

  useEffect(() => {
    // Gọi API để lấy thông tin chuyến đi theo id
    const fetchTrip = async () => {
      try {
        const response = await api.get(`/trips/${id}/seats`);
        const newData = response.data.data
        console.log('responsetrips', newData)
        setTripData(newData.trip);
        setSeatData(newData.seats)
      } catch (error) {
        console.error('Lỗi khi gọi API:', error);
      }
    };

    fetchTrip();
  }, [id]);



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
  const departure = queryParams.get('departure');
  const destination = queryParams.get('destination');
  const date = queryParams.get('date');

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
    navigate('/home');
  };

  // Handle view bookings
  const handleViewBookings = () => {
    setShowSuccessModal(false);
    navigate('/my-bookinglogin');
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
        const response = await api.get(`/trips/${tripId}/seats`);
        setTripSeats(response.data.data.seats);
        setLoading(false);
      } catch (error) {
        console.error("Error loading seat data:", error);
        setTripSeats([]);
        setLoading(false);
      }
    };

    loadSeats();
  }, [tripId, busName, busTime, busPrice, isAuthenticated, user]);

  // Toggle seat selection
  const toggleSeat = (seatId) => {
    setSelectedSeats(prevSelectedSeats => {
      if (prevSelectedSeats.includes(seatId)) {
        return prevSelectedSeats.filter(id => id !== seatId);
      } else {
        return [...prevSelectedSeats, seatId];
      }
    });
  };

  // Calculate total price
  const totalPrice = selectedSeats.length * tripData?.price;

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!isAuthenticated) {
      showNotification("Vui lòng đăng nhập để đặt vé!", "error");
      return;
    }

    if (!passengerName || !passengerPhone || !passengerEmail) {
      setBookingResult({
        message: "Vui lòng điền đầy đủ thông tin!",
        isError: true
      });
      return;
    }

    if (selectedSeats.length === 0) {
      setBookingResult({
        message: "Vui lòng chọn ít nhất một ghế!",
        isError: true
      });
      return;
    }

    setSubmitting(true); // Disable form

    try {
      const bookingData = {
        trip_id: tripData.id,
        seat_ids: selectedSeats,
        passenger_name: passengerName,
        passenger_phone: passengerPhone,
        passenger_email: passengerEmail,
        payment_method: paymentMethod
      };

      const response = await api.post('/bookings', bookingData);
      const result = response.data.data;

      // Nếu là thanh toán qua vnpay/momo → redirect
      if (result.payment_url) {
        window.location.href = result.payment_url;
        return;
      }

      // Trường hợp thanh toán tiền mặt hoặc không có URL thanh toán
      setBookingCode(result.booking.booking_code);
      setShowSuccessModal(true);

    } catch (error) {
      console.error("Lỗi đặt vé:", error);
      if (error.response && error.response.data && error.response.data.errors) {
        setBookingResult({
          message: "Lỗi: " + JSON.stringify(error.response.data.errors),
          isError: true
        });
      } else if (error.response && error.response.data && error.response.data.message) {
        setBookingResult({
          message: `Lỗi: ${error.response.data.message}`,
          isError: true
        });
      } else {
        setBookingResult({
          message: `Đặt vé thất bại: ${error.message}`,
          isError: true
        });
      }
    } finally {
      setSubmitting(false); // Enable form
    }
  };

  // Tự động điền thông tin khách hàng khi đã đăng nhập
  useEffect(() => {
    if (isAuthenticated && user) {
      setPassengerName(user.name || '');
      setPassengerPhone(user.phone || '');
      setPassengerEmail(user.email || '');
    }
  }, [isAuthenticated, user]);

  // If trip data is missing, show error
  if (!tripData) {
    return (
      <div className="ticket-detaillogin">
        <header>
          <div className="container">
            <h1>Phương Thanh Express</h1>
            <p to="/booking-results" className="back-link">Quay lại Tìm kiếm</p>
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
    <div className="ticket-detaillogin">
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to={`/booking-resultslogin?departure=${departure}&destination=${destination}&date=${date}`} className="back-link">Quay lại Tìm kiếm</Link>
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
            <h3>Thông tin chuyến xe</h3>
            <p className="bus-info">Chuyến xe: {tripData?.vehicle?.name} | Giờ khởi hành: {formatTime(tripData?.departure_time)}</p>
            <p> Giá vé: {formatCurrency(tripData.price)}</p>
            <p className="total-price">Tổng tiền: {formatCurrency(totalPrice)}</p>

            {/* Sơ đồ chọn ghế */}
            <h3>Chọn ghế ngồi</h3>
            <div className="seat-map">
              {seatData.map((seat) => (
                <div
                  key={seat.id}
                  className={`seat ${seat.status === 'taken' || seat.booking_status === 'booked'
                    ? 'taken'
                    : selectedSeats.includes(seat.id)
                      ? 'selected'
                      : 'available'
                    }`}
                  onClick={() =>
                    seat.status !== 'taken' && seat.booking_status !== 'booked' &&
                    toggleSeat(seat.id)
                  }
                  style={{ pointerEvents: seat.status === 'taken' || seat.booking_status === 'booked' ? 'none' : 'auto' }}
                >
                  {seat.seat_number}
                </div>
              ))}
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
                />
              </div>
              <div className="form-group">
                <label htmlFor="paymentMethod">Phương thức thanh toán:</label>
                <select
                  id="paymentMethod"
                  value={paymentMethod}
                  onChange={(e) => setPaymentMethod(e.target.value)}
                >
                  <option value="cash">Thanh toán khi lên xe</option>
                  <option value="vnpay">VNPAY</option>
                  <option value="momo">MOMO</option>
                </select>
              </div>
              <button
                type="submit"
                className={`btn-modern ${submitting ? 'disabled' : ''}`}
                disabled={submitting}
              >
                {submitting ? 'Đang xử lý...' : 'Xác nhận đặt vé'}
              </button>
            </form>

            {!isAuthenticated && (
              <div className="login-reminder">
                <p>
                  Bạn cần <Link to={`/login?redirect=${encodeURIComponent(location.pathname + location.search)}`}>đăng nhập</Link> để đặt vé.
                  Chưa có tài khoản? <Link to="/register">Đăng ký ngay</Link>
                </p>
              </div>
            )}

            {bookingResult.message && (
              <div className={`booking-resultslogin ${bookingResult.isError ? 'error' : 'success'}`}>
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