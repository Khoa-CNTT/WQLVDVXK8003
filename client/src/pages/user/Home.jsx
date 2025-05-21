import React, { useState, useEffect, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import './Home.css';
import { Storage } from '../../constant/storage';

const Home = () => {
  const navigate = useNavigate();
  const { user, isAuthenticated, logout } = useAuth(); 
  // State for slider
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [isPlaying, setIsPlaying] = useState(true);
  const [chatbotOpen, setChatbotOpen] = useState(false);
  const [chatMessages, setChatMessages] = useState([]);
  const [chatInput, setChatInput] = useState('');
  // Thêm state để kiểm tra đăng nhập
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userData, setUserData] = useState(null);

  // Kiểm tra trạng thái đăng nhập khi component mount
  useEffect(() => {
    // Giả sử bạn lưu thông tin đăng nhập trong localStorage
    const userInfo = localStorage.getItem('userInfo');
    if (userInfo) {
      setIsLoggedIn(true);
      setUserData(JSON.parse(userInfo));
    }
  }, []);

  // Images for slider
  const images = [
    "https://img.pikbest.com/wp/202405/bus-station-white-coach-touring-parked-by-a-against-backdrop-3d-illustration_9847285.jpg!w700wp",
    "https://lh4.googleusercontent.com/proxy/Zwn6io0vGPYQPl0qVTFsH86pmMVf4LZBhO8h7LoZ-x3j2wpWPgJiWM-t6dsso_m2JFRnpwjOH0YqY72gcQStK_rBTDrU_7B5",
    "https://carshop.vn/wp-content/uploads/2022/07/anh-xe-giuong-nam-37.jpg"
  ];

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
  const viewRouteDetail = (departure, destination) => {
    const today = new Date().toISOString().split("T")[0];
    navigate(`/booking-resultslogin?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(today)}`);
  };

  // View route list
  const viewRouteList = (departure, destination) => {
    const today = new Date().toISOString().split("T")[0];
    navigate(`/booking-resultslogin?departure=${encodeURIComponent(departure)}&destination=${encodeURIComponent(destination)}&date=${encodeURIComponent(today)}`);
  };

  // Image slider functions
  const changeImage = useCallback((next = true) => {
    setCurrentImageIndex(prevIndex => 
      next 
        ? (prevIndex + 1) % images.length 
        : (prevIndex - 1 + images.length) % images.length
    );
  }, [images.length]);

  const startSlider = useCallback(() => {
    setIsPlaying(true);
  }, []);

  const stopSlider = useCallback(() => {
    setIsPlaying(false);
  }, []);

  // Set up image slider interval
  useEffect(() => {
    let interval;
    if (isPlaying) {
      interval = setInterval(() => changeImage(true), 3000);
    }
    return () => clearInterval(interval);
  }, [isPlaying, changeImage]);

  // Chatbot functions
  const toggleChatbot = () => {
    setChatbotOpen(prev => {
      const newState = !prev;
      if (newState && chatMessages.length === 0) {
        // Add welcome message if opening for first time
        setChatMessages([{
          text: "Xin chào! Tôi là chatbot của Nhà xe Phương Thanh.<br>Tôi có thể giúp bạn:<br>1. Đặt vé xe<br>2. Xem lịch trình<br>3. Tìm hiểu về chúng tôi<br>4. Hỗ trợ khác<br>Bạn cần tôi giúp gì ạ?",
          type: "bot"
        }]);
      }
      return newState;
    });
  };

  const handleChatInputChange = (e) => {
    setChatInput(e.target.value);
  };

  const handleChatSubmit = async (e) => {
    if (e.key === "Enter" && chatInput.trim()) {
      const userMessage = chatInput.trim();
      setChatMessages(prev => [...prev, { text: userMessage, type: "user" }, { text: "Đang trả lời...", type: "loading" }]);
      setChatInput("");
      try {
        const response = await fetch('/api/v1/chatbot/query', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ query: userMessage, session_id: '1' })
        });
        const data = await response.json();
        const botResponse = data?.data?.data?.message || data?.data?.message || data?.message || "Bot không trả lời.";
        setChatMessages(prev => {
          // Thay thế tin nhắn loading cuối cùng bằng câu trả lời thực tế
          const newMsgs = [...prev];
          const idx = newMsgs.findIndex(m => m.type === 'loading');
          if (idx !== -1) newMsgs[idx] = { text: botResponse, type: "bot" };
          return newMsgs;
        });
      } catch (error) {
        setChatMessages(prev => {
          const newMsgs = [...prev];
          const idx = newMsgs.findIndex(m => m.type === 'loading');
          if (idx !== -1) newMsgs[idx] = { text: "Đã xảy ra lỗi khi truy vấn bot. Vui lòng thử lại sau.", type: "bot" };
          return newMsgs;
        });
      }
    }
  };

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
            <li><a href="#" onClick={() => viewRouteDetail('Đà Nẵng', 'Quảng Bình')} className="dropdown-item">Đà Nẵng - Quảng Bình</a></li>
            <li><a href="#" onClick={() => viewRouteDetail('Đà Nẵng', 'Nghệ An')} className="dropdown-item">Đà Nẵng - Nghệ An</a></li>
            <li><a href="#" onClick={() => viewRouteDetail('Đà Nẵng', 'Hà Giang')} className="dropdown-item">Đà Nẵng - Hà Giang</a></li>
            <li><a href="#" onClick={() => viewRouteDetail('Đà Nẵng', 'Hồ Chí Minh')} className="dropdown-item">Đà Nẵng - HCM</a></li>
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
                <li>📌 <strong>Đặt vé:</strong> <span className="phone-number">0905.3333.33</span></li>
                <li>📦 <strong>Gửi hàng:</strong> <span className="phone-number">0905.888.888</span> (Mạnh)</li>
                <li>🚛 <strong>Thuê xe chở hàng:</strong> <span className="phone-number">0905.1111.11</span></li>
                <li>📜 <strong>Hợp đồng thuê xe:</strong> <span className="phone-number">0905.2222.22</span> (Hùng)</li>
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

      {/* Chương trình khuyến mãi */}
      <section className="promotion-section">
        <h2 className="section-title">CHƯƠNG TRÌNH KHUYẾN MÃI</h2>
        <div className="promotion-grid">
          <div className="promotion-card">
            <h3 className="promotion-title">KHÁCH HÀNG THÂN THIẾT</h3>
            <ul className="promotion-list">
              <li className="promotion-item">🎁 <span className="promotion-text">Giảm giá <strong>40%</strong> khi tích lũy được từ <strong>20 chuyến</strong>.</span></li>
              <li className="promotion-item">🎁 <span className="promotion-text">Giảm giá <strong>20%</strong> khi tích lũy được từ <strong>15 chuyến</strong>.</span></li>
              <li className="promotion-item">🎁 <span className="promotion-text">Giảm giá <strong>10%</strong> khi tích lũy được từ <strong>10 chuyến</strong>.</span></li>
            </ul>
            <button className="btn-modern">Tìm hiểu ngay</button>
          </div>
          <div className="promotion-card">
            <h3 className="promotion-title">BLIND BOX</h3>
            <ul className="promotion-list">
              <li className="promotion-item">🎁 <span className="promotion-text"><strong>1 iPhone 15</strong> phiên bản mới nhất.</span></li>
              <li className="promotion-item">🎁 <span className="promotion-text">Hơn <strong>5000</strong> mã giảm giá có mệnh giá lên tới <strong>100.000đ</strong>.</span></li>
              <li className="promotion-item">🎁 <span className="promotion-text">Nhiều <strong>phần quà nhỏ khác</strong> đang chờ bạn khám phá.</span></li>
            </ul>
            <button className="btn-modern">Tìm hiểu ngay</button>
          </div>
        </div>
      </section>

      {/* Chatbot */}
      {chatbotOpen && (
        <div className="chatbot">
          <div className="chatbot-header">
            <h3 className="chatbot-title">Hỗ trợ Phương Thanh Express</h3>
            <button onClick={toggleChatbot} className="chatbot-close">✖</button>
          </div>
          <div className="chatbot-messages">
            {chatMessages.map((message, index) => (
              <div 
                key={index} 
                className={`message ${message.type === "user" ? "user-message" : "bot-message"}`}
              >
                <span dangerouslySetInnerHTML={{ __html: message.text }} />
              </div>
            ))}
          </div>
          <div className="chatbot-input-container">
            <input 
              type="text" 
              placeholder="Nhập câu hỏi của bạn..." 
              className="chatbot-input"
              value={chatInput}
              onChange={handleChatInputChange}
              onKeyPress={handleChatSubmit}
            />
          </div>
        </div>
      )}
      <button 
        onClick={toggleChatbot} 
        className={`chatbot-toggle ${chatbotOpen ? 'hidden' : ''}`}
      >
        💬 Chat
      </button>

      {/* Footer */}
      <footer>
        <div className="footer-grid container">
          <div className="footer-column">
            <h3 className="footer-title">NHÀ XE PHƯƠNG THANH ĐÀ NẴNG</h3>
            <div className="social-links">
              <a href="#" className="social-link">📘</a>
              <a href="#" className="social-link">❌</a>
              <a href="#" className="social-link">▶️</a>
              <a href="#" className="social-link">🔗</a>
            </div>
            <div className="map-container">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.1104391547477!2d108.19966061484894!3d16.059718588885864!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3142190fbfdfd6c7%3A0x33bd6048f8e47311!2zxJDDoCBO4bq1bmcsIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1652344895954!5m2!1svi!2s" className="map-iframe"></iframe>
            </div>
          </div>
          <div className="footer-column">
            <h3 className="footer-title">CHÍNH SÁCH CÔNG TY</h3>
            <ul className="footer-links">
              <li><a href="#" className="footer-link">Giới thiệu</a></li>
              <li><a href="#" className="footer-link">Liên hệ</a></li>
              <li><a href="#" className="footer-link">Điều khoản sử dụng</a></li>
              <li><a href="#" className="footer-link">Chính sách vận chuyển</a></li>
              <li><Link to="/security" className="footer-link">Chính sách bảo mật</Link></li>
            </ul>
          </div>
          <div className="footer-column">
            <h3 className="footer-title">PHƯƠNG THỨC THANH TOÁN</h3>
            <div className="payment-methods">
              <img src="https://th.bing.com/th?q=Momo+Icon+App+PNG&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" className="payment-logo" />
              <img src="https://th.bing.com/th?q=Vnpay+Logo.png&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" className="payment-logo" />
            </div>
          </div>
          <div className="footer-column">
            <h3 className="footer-title">LIÊN HỆ</h3>
            <p className="contact-info">Công ty TNHH Vận Tải <strong>Phương Thanh</strong></p>
            <p className="contact-info">12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.</p>
            <p className="contact-info">📞 Mã số thuế: <strong>1111111</strong></p>
            <p className="contact-info">📞 Hotline: <strong>0905.999999</strong></p>
            <p className="contact-info">✉️ Email: <strong>phuongthanh@gmail.com</strong></p>
          </div>
        </div>
        <div className="copyright">© Copyright 2025. Phương Thanh Express</div>
      </footer>
    </div>
  );
};

export default Home;