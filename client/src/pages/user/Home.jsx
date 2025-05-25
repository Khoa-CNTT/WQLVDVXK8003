import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import './Home.css';
import { Storage } from '../../constant/storage';
import { useApi } from '../../hooks/useApi';
import images from '../../data/sliderImages'
import useImageSlider from '../../components/SliderCPN/useImageSlider';
import FooterHome from '../../components/FooterHome/FooterHome';
import Chatbot from '../../components/Chatbot/Chatbot';

const Home = () => {
  const navigate = useNavigate();
  const { user, isAuthenticated, logout } = useAuth();
  // Thêm state để kiểm tra đăng nhập
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userData, setUserData] = useState(null);
  const {
    currentImageIndex,
    changeImage,
    startSlider,
    stopSlider,
    isPlaying
  } = useImageSlider(images);

  const [lines, setLines] = useState([]);
  const api = useApi()

  useEffect(() => {
    const fetchLines = async () => {
      try {
        const response = await api.get('/lines');
        console.log('responseLine', response)
        setLines(response.data);
      } catch (error) {
        console.error('Lỗi khi fetch tuyến:', error);
      }
    };

    fetchLines();
  }, []);

  // Kiểm tra trạng thái đăng nhập khi component mount
  useEffect(() => {
    // Giả sử bạn lưu thông tin đăng nhập trong localStorage
    const userInfo = localStorage.getItem('userInfo');
    if (userInfo) {
      setIsLoggedIn(true);
      setUserData(JSON.parse(userInfo));
    }
  }, []);


  // Handle booking form submit
  const handleBookingSubmit = (e) => {
    e.preventDefault();
    const departure = e.target.departure.value;
    const destination = e.target.destination.value;
    const date = e.target.date.value;
    const today = new Date().toISOString().split("T")[0];

    if (date < today) {
      showNotification("Không được chọn ngày trong quá khứ.", "error");
      return;
    }

    if (departure && destination && date) {
      navigate(`/booking-resultslogin?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(date)}`);
    } else {
      showNotification("Vui lòng chọn đầy đủ thông tin.", "error");
    }
  };

  // View route detail
  const viewLineDetail = (lineId) => {
    navigate(`/booking-resultslogin?line_id=${lineId}`);
  };

  // View route list
  const viewRouteList = (departure, destination) => {
    const today = new Date().toISOString().split("T")[0];
    navigate(`/booking-resultslogin?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(today)}`);
  };

  // Chatbot functions

  // Notification function
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

  // Hàm đăng xuất
  const handleLogout = () => {
    try {
      // Gọi hàm logout từ AuthContext
      logout();

      // Xóa tất cả dữ liệu người dùng khỏi localStorage
      localStorage.removeItem('userInfo');
      localStorage.removeItem(Storage.AUTH_DATA);

      // Cập nhật state trong component
      setIsLoggedIn(false);
      setUserData(null);

      // Hiển thị thông báo
      showNotification("Bạn đã đăng xuất thành công", "success");

      // Chuyển hướng người dùng
      navigate('/?logout=true');

      // Force reload page để đảm bảo tất cả state được reset
      window.location.reload();
    } catch (error) {
      console.error("Lỗi khi đăng xuất:", error);

      // Xử lý backup để đảm bảo user vẫn logout được
      localStorage.clear(); // Xóa tất cả các item trong localStorage
      setIsLoggedIn(false);
      setUserData(null);
      navigate('/');
    }
  };

  // Lấy tên người dùng từ thông tin đăng nhập
  const getUserName = () => {
    // Ưu tiên lấy từ useAuth context
    if (user && user.name) {
      return user.name;
    }
    // Nếu không có trong context, lấy từ userData state
    if (userData && userData.name) {
      return userData.name;
    }
    // Trường hợp mặc định
    return 'Tài Khoản';
  };

  return (
    <div className="introductions-container">
      {/* Header */}
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <nav>
            <ul className="nav-menu">
              <li><Link to="/home" className="nav-link">Trang Chủ</Link></li>
              {/* Menu Tuyến Hoạt Động với Dropdown */}
              <li className="dropdown">
                <a href="#" className="nav-link dropdown-toggle">
                  Tuyến Hoạt Động
                  <svg className="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </a>
                <ul className="dropdown-menu">
                  {lines.map((line) => (
                    <li key={line.id}>
                      <Link to={`/booking-resultslogin?line_id=${line.id}`} className="dropdown-item">
                        {line.departure} → {line.destination}
                      </Link>
                    </li>
                  ))}
                </ul>
              </li>
              <li><Link to="/utilitiesLogin" className="nav-link">Tiện ích</Link></li>
              <li><Link to="/securityLogin" className="nav-link">Chính sách</Link></li>
              {/* Profile Dropdown - Hiển thị tên người dùng thay vì "Đăng Nhập" */}
              <li id="auth-menu" className="dropdown">
                <a href="#" className="nav-link dropdown-toggle">
                  {getUserName()}
                  <svg className="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </a>
                <ul className="dropdown-menu">
                  <li><Link to="/profile" className="dropdown-item">Thông tin cá nhân</Link></li>
                  <li><Link to="/my-bookinglogin" className="dropdown-item">Vé của tôi</Link></li>
                  <li>
                    <a
                      href="#"
                      onClick={(e) => {
                        e.preventDefault(); // Ngăn chuyển trang khi click vào thẻ a
                        handleLogout();
                      }}
                      className="dropdown-item"
                    >
                      Đăng xuất
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </nav>
        </div>
      </header>
      {/* Giới thiệu */}
      <section className="intro-section">
        <div className="container grid-layout">
          <div className="intro-content">
            <h2 className="section-title">Giới thiệu <span className="title-highlight">PHƯƠNG THANH EXPRESS</span></h2>
            <p className="intro-text">
              Trải qua <strong className="highlight">hơn 10 năm</strong> hoạt động, Công ty Cổ phần vận tải <strong>Phương Thanh</strong> với thương hiệu <strong className="highlight">Phương Thanh Express</strong> đã góp phần thúc đẩy giao thương giữa <strong className="highlight">TP. Đà Nẵng</strong> và các tỉnh phía Bắc.
            </p>
            <p className="intro-text">
              Chúng tôi cam kết <strong>sự an toàn</strong>, <strong>chất lượng dịch vụ</strong> và luôn lựa chọn <strong>đội ngũ nhân viên</strong> tận tâm để đảm bảo khách hàng có <strong>sự hài lòng, an tâm</strong> và <strong>trải nghiệm tốt nhất</strong>.
            </p>
            <p className="intro-text">
              Cảm ơn Quý khách đã <strong className="highlight">luôn tin cậy</strong> và ủng hộ chúng tôi. Chúng tôi không ngừng <strong>cải thiện</strong> để phục vụ Quý khách tốt hơn.
            </p>
            <p className="intro-quote">Kính chúc Quý Khách luôn <span className="quote-highlight">Bình An Trên Vạn Dặm.</span></p>
            <div className="contact-info-box">
              <h3 className="contact-title">📞 Thông tin liên hệ</h3>
              <ul className="contact-list">
                <li>📌 <strong>Đặt vé:</strong> <span className="phone-number">0905.3333.33</span>(Huy)</li>
                <li>📦 <strong>Gửi hàng:</strong> <span className="phone-number">0905.888.888</span> (Trí)</li>
                <li>🚛 <strong>Thuê xe chở hàng:</strong> <span className="phone-number">0905.1111.11</span> (Hải)</li>
                <li>📜 <strong>Hợp đồng thuê xe:</strong> <span className="phone-number">0905.2222.22</span> (Dũng)</li>
                <li>📜 <strong>Chăm sóc khách hàng:</strong> <span className="phone-number">0905.3333.33</span> (Phong)</li>
              </ul>
            </div>
          </div>
          <div className="slider-container">
            <div className="image-container">
              <img
                src={images[currentImageIndex]}
                alt="Xe khách Phương Thanh Express"
                className={`slider-image ${isPlaying ? '' : 'paused'}`}
              />
            </div>
            <div className="slider-controls">
              <button onClick={() => { stopSlider(); changeImage(false); }} className="slider-btn prev-btn">◀</button>
              <button onClick={startSlider} className="slider-btn play-btn">▶</button>
              <button onClick={stopSlider} className="slider-btn pause-btn">⏸</button>
              <button onClick={() => { stopSlider(); changeImage(true); }} className="slider-btn next-btn">▶</button>
            </div>
          </div>
        </div>
      </section>

      {/* Đặt vé xe trực tuyến */}
      <section className="booking-section">
        <h2 className="section-title">ĐẶT VÉ XE TRỰC TUYẾN</h2>
        <p className="hotline-text">📞 Tổng đài hỗ trợ: <span className="hotline-number">0905.999999</span></p>
        <form className="booking-form" onSubmit={handleBookingSubmit}>
          <div className="form-group">
            <label htmlFor="departure" className="form-label">Nơi đi:</label>
            <select id="departure" name="departure" className="form-control">
              <option value="Đà Nẵng">Đà Nẵng</option>
              <option value="Quảng Bình">Quảng Bình</option>
              <option value="Nghệ An">Nghệ An</option>
              <option value="Hà Giang">Hà Giang</option>
              <option value="Hồ Chí Minh">Hồ Chí Minh</option>
            </select>
          </div>
          <div className="form-group">
            <label htmlFor="destination" className="form-label">Nơi đến:</label>
            <select id="destination" name="destination" className="form-control">
              <option value="Quảng Bình">Quảng Bình</option>
              <option value="Nghệ An">Nghệ An</option>
              <option value="Hà Giang">Hà Giang</option>
              <option value="Hồ Chí Minh">Hồ Chí Minh</option>
              <option value="Đà Nẵng">Đà Nẵng</option>
            </select>
          </div>
          <div className="form-group">
            <label htmlFor="date" className="form-label">Ngày đi:</label>
            <input
              type="date"
              id="date"
              name="date"
              className="form-control"
              defaultValue={new Date().toISOString().split("T")[0]}
            />
          </div>
          <button type="submit" className="btn-modern">Tìm chuyến xe</button>
        </form>
      </section>

      {/* Hướng dẫn đón xe không đợi chờ */}
      <section className="pickup-guide-section">
        <h2 className="section-title">ĐÓN XE KHÔNG ĐỢI CHỜ</h2>
        <div className="steps-grid">
          <div className="step-card">
            <h3 className="step-title">BƯỚC 1</h3>
            <p className="step-desc"><strong>KHTT.EXPRESS.COM</strong></p>
            <button className="btn-modern">Đi tới KHTT</button>
          </div>
          <div className="step-card">
            <h3 className="step-title">BƯỚC 2</h3>
            <p className="step-desc">Đăng ký tài khoản</p>
            <button onClick={() => navigate('/register')} className="btn-modern">Đăng ký</button>
          </div>
          <div className="step-card">
            <h3 className="step-title">BƯỚC 3</h3>
            <p className="step-desc">Quản lý vé xe</p>
            <button onClick={() => navigate('/my-bookinglogin')} className="btn-modern">Quản lý vé xe</button>
          </div>
          <div className="step-card">
            <h3 className="step-title">BƯỚC 4</h3>
            <p className="step-desc">Xem vị trí trên Google Maps</p>
            <button className="btn-modern">Xem vị trí</button>
          </div>
        </div>
      </section>

      {/* Cơ sở vật chất */}
      <section className="facilities-section">
        <h2 className="section-title">CƠ SỞ VẬT CHẤT</h2>
        <div className="facilities-grid">
          <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-1.jpg" alt="Nội thất xe" className="facility-image" />
          <img src="https://th.bing.com/th/id/OIP.vzTEjN22_1836csGgK-HSQHaER?w=292&h=180&c=7&r=0&o=5&dpr=1.3&pid=1.7" alt="Nội thất xe giường nằm" className="facility-image" />
          <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/xe-trung-chuyen-hieu-hoa.jpg" alt="Xe trung chuyển" className="facility-image" />
        </div>
      </section>

      {/* Danh sách tuyến hoạt động */}
      <section className="routes-section">
        <h2 className="section-title">DANH SÁCH TUYẾN HOẠT ĐỘNG</h2>
        <div className="routes-grid">
          <div className="route-card">
            <p className="route-name">Đà Nẵng - Quảng Bình</p>
            <p className="route-desc">Thông tin xe, lịch trình, giá vé</p>
            <button onClick={() => viewRouteList('Đà Nẵng', 'Quảng Bình')} className="btn-modern">Xem chi tiết</button>
          </div>
          <div className="route-card">
            <p className="route-name">Đà Nẵng - Nghệ An</p>
            <p className="route-desc">Thông tin xe, lịch trình, giá vé</p>
            <button onClick={() => viewRouteList('Đà Nẵng', 'Nghệ An')} className="btn-modern">Xem chi tiết</button>
          </div>
          <div className="route-card">
            <p className="route-name">Đà Nẵng - Hà Giang</p>
            <p className="route-desc">Thông tin xe, lịch trình, giá vé</p>
            <button onClick={() => viewRouteList('Đà Nẵng', 'Hà Giang')} className="btn-modern">Xem chi tiết</button>
          </div>
          <div className="route-card">
            <p className="route-name">Đà Nẵng - HCM</p>
            <p className="route-desc">Thông tin xe, lịch trình, giá vé</p>
            <button onClick={() => viewRouteList('Đà Nẵng', 'Hồ Chí Minh')} className="btn-modern">Xem chi tiết</button>
          </div>
        </div>
      </section>
      {/* Chatbot */}
      <Chatbot />
      {/* Footer */}
      <FooterHome />
    </div>
  );
};

export default Home;