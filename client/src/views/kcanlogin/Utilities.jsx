import React, { useState, useEffect } from 'react';
import './Utilities.css';

const Utilities = () => {
  const [isChatbotOpen, setIsChatbotOpen] = useState(false);
  const [messages, setMessages] = useState([]);

  const responses = {
    "wifi": "Wi-Fi trên xe của chúng tôi có tốc độ cao, đủ để bạn lướt web, xem phim hoặc làm việc.",
    "giường nằm": "Giường nằm được thiết kế rộng rãi, êm ái với chăn gối sạch sẽ, đảm bảo bạn có giấc ngủ ngon.",
    "nước uống": "Chúng tôi cung cấp nước uống miễn phí suốt chuyến đi để bạn luôn thoải mái.",
    "điều hòa": "Tất cả xe của chúng tôi đều được trang bị hệ thống điều hòa hiện đại, đảm bảo không gian thoáng mát trong suốt hành trình.",
    "trung chuyển": "Dịch vụ xe trung chuyển hỗ trợ đưa đón tận nơi tại các điểm trung chuyển thuận tiện.",
    "giải trí" : "Hệ thống giải trí trên xe bao gồm màn hình LED và tai nghe cá nhân để bạn thư giãn với phim ảnh, âm nhạc.",
    "đồ ăn": "Hiện tại chúng tôi không phục vụ đồ ăn trên xe, nhưng bạn có thể mang theo đồ ăn nhẹ và xe sẽ dừng ở các trạm nghỉ dọc đường.",
    "xin chào": "Chào bạn! Phương Thanh Express rất vui được hỗ trợ bạn hôm nay.",
    "cảm ơn": "Không có gì, rất vui được hỗ trợ bạn! Nếu có thắc mắc gì thêm, đừng ngần ngại hỏi tôi nhé."
  };

  useEffect(() => {
    if (isChatbotOpen && messages.length === 0) {
      setMessages([{ text: "Xin chào! Tôi là trợ lý ảo của Phương Thanh Express. Tôi có thể giúp gì cho bạn về các tiện ích trên xe?", type: "bot" }]);
    }
  }, [isChatbotOpen]);

  const handleChatbotToggle = () => {
    setIsChatbotOpen(!isChatbotOpen);
  };

  const handleKeyPress = async (e) => {
    if (e.key === "Enter" && e.target.value.trim()) {
      const userMessage = e.target.value.trim();
      setMessages([...messages, { text: userMessage, type: "user" }]);
      e.target.value = "";

      try {
        let foundResponse = false;
        for (const [key, value] of Object.entries(responses)) {
          if (userMessage.toLowerCase().includes(key)) {
            setTimeout(() => {
              setMessages(prev => [...prev, { text: value, type: "bot" }]);
            }, 500);
            foundResponse = true;
            break;
          }
        }

        if (!foundResponse) {
          setTimeout(() => {
            setMessages(prev => [...prev, { 
              text: "Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn có thể hỏi cụ thể hơn về tiện ích hoặc liên hệ hotline 0905.999999 để được hỗ trợ!", 
              type: "bot" 
            }]);
          }, 500);
        }
      } catch (error) {
        console.error('Lỗi gửi truy vấn đến chatbot:', error);
        setTimeout(() => {
          setMessages(prev => [...prev, { 
            text: "Xin lỗi, hiện tại tôi không thể xử lý yêu cầu của bạn. Vui lòng thử lại sau hoặc liên hệ hotline 0905.999999.", 
            type: "bot" 
          }]);
        }, 500);
      }
    }
  };

  return (
    <div className="utilities">
      {/* Header */}
      <header className="header">
        <div className="container">
          <h1 className="title">Phương Thanh Express</h1>
          <nav>
            <ul className="nav-list">
              <li><a href="/" className="nav-link"> Trang Chủ</a></li>


            </ul>
          </nav>
        </div>
      </header>

      {/* Giới thiệu tiện ích */}
      <section className="utilities-section">
        <h2 className="section-title">TIỆN ÍCH TRÊN XE</h2>
        <p className="section-description">
          Chúng tôi luôn nỗ lực mang đến cho khách hàng những trải nghiệm thoải mái và tiện nghi nhất trên mọi hành trình.
        </p>
        <div className="utilities-grid">
          <div className="card">
            <img src="http://thietbiwifi4g.com/assets/shops/wifi_4g/wifi_xekhach/lap-dat-wifi-xe-khach-1.jpg" alt="Wi-Fi miễn phí" className="utility-image" />
            <h3 className="card-title">Wi-Fi Miễn Phí</h3>
            <p className="card-description">Kết nối internet tốc độ cao suốt hành trình để bạn luôn giữ liên lạc hoặc giải trí.</p>
          </div>
          <div className="card">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-1.jpg" alt="Giường nằm cao cấp" className="utility-image" />
            <h3 className="card-title">Giường Nằm Cao Cấp</h3>
            <p className="card-description">Giường nằm êm ái, rộng rãi với chăn gối sạch sẽ, mang lại giấc ngủ trọn vẹn.</p>
          </div>
          <div className="card">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/09/noi-that-xe-hieu-hoa-1.jpg" alt="Nước uống miễn phí" className="utility-image" />
            <h3 className="card-title">Nước Uống Miễn Phí</h3>
            <p className="card-description">Cung cấp nước uống miễn phí suốt chuyến đi để bạn luôn sảng khoái.</p>
          </div>
          <div className="card">
            <img src="https://tanquangdung.com/wp-content/uploads/2024/05/tan-quang-dung-6-1200x700.jpg" alt="Điều hòa mát lạnh" className="utility-image" />
            <h3 className="card-title">Điều Hòa Mát Lạnh</h3>
            <p className="card-description">Hệ thống điều hòa hiện đại, đảm bảo không gian thoáng mát trong suốt hành trình.</p>
          </div>
          <div className="card">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/xe-trung-chuyen-hieu-hoa.jpg" alt="Xe trung chuyển" className="utility-image" />
            <h3 className="card-title">Xe Trung Chuyển</h3>
            <p className="card-description">Hỗ trợ đưa đón tận nơi tại các điểm trung chuyển thuận tiện.</p>
          </div>
          <div className="card">
            <img src="https://hieuhoaexpress.com/wp-content/uploads/2019/08/noi-that-xe-24-phong-doi-hieu-hoa-2.jpg" alt="Hệ thống giải trí" className="utility-image" />
            <h3 className="card-title">Hệ Thống Giải Trí</h3>
            <p className="card-description">Màn hình LED và tai nghe cá nhân để bạn thư giãn với phim ảnh, âm nhạc.</p>
          </div>
        </div>
      </section>

      {/* Hỗ trợ khách hàng */}
      <section className="support-section">
        <h2 className="section-title">HỖ TRỢ KHÁCH HÀNG</h2>
        <p className="section-description">Liên hệ với chúng tôi để được tư vấn thêm về các tiện ích và dịch vụ.</p>
        <div className="support-buttons">
          <button className="btn-modern">📞 Gọi hotline: 0905.999999</button>
          <button className="btn-modern" onClick={handleChatbotToggle}>💬 Chat ngay</button>
        </div>
      </section>

      {/* Chatbot */}
      <div className={`chatbot ${isChatbotOpen ? '' : 'hidden'}`}>
        <div className="chatbot-header">
          <h3 className="chatbot-title">Hỗ trợ Phương Thanh Express</h3>
          <button className="chatbot-close" onClick={handleChatbotToggle}>✖</button>
        </div>
        <div className="chatbot-messages">
          {messages.map((msg, index) => (
            <div key={index} className={`message ${msg.type === 'user' ? 'user-message' : 'bot-message'}`}>
              {msg.text}
            </div>
          ))}
        </div>
        <div className="chatbot-input">
          <input
            type="text"
            placeholder="Nhập câu hỏi của bạn..."
            className="input-field"
            onKeyPress={handleKeyPress}
          />
        </div>
      </div>
      {!isChatbotOpen && (
        <button className="chatbot-toggle" onClick={handleChatbotToggle}>
          💬 Chat
        </button>
      )}

      {/* Footer */}
      <footer className="footer">
        <div className="container">
          <div className="footer-grid">
            <div>
              <h3 className="footer-title">NHÀ XE PHƯƠNG THANH ĐÀ NẴNG</h3>
              <div className="social-links">
                <a href="#" className="social-icon">📘</a>
                <a href="#" className="social-icon">❌</a>
                <a href="#" className="social-icon">▶️</a>
                <a href="#" className="social-icon">🔗</a>
              </div>
            </div>
            <div>
              <h3 className="footer-title">CHÍNH SÁCH CÔNG TY</h3>
              <ul className="footer-links">
                <li><a href="../index.html" className="footer-link">Giới thiệu</a></li>
                <li><a href="../index.html" className="footer-link">Liên hệ</a></li>
                <li><a href="#" className="footer-link">Điều khoản sử dụng</a></li>
                <li><a href="security.html" className="footer-link">Chính sách bảo mật</a></li>
              </ul>
            </div>
            <div>
              <h3 className="footer-title">PHƯƠNG THỨC THANH TOÁN</h3>
              <div className="payment-methods">
                <img src="https://th.bing.com/th?q=Momo+Icon+App+PNG&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" className="payment-icon" alt="Momo" />
                <img src="https://th.bing.com/th?q=Vnpay+Logo.png&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247" className="payment-icon" alt="Vnpay" />
              </div>
            </div>
            <div>
              <h3 className="footer-title">LIÊN HỆ</h3>
              <p className="contact-info">Công ty TNHH Vận Tải <strong>Phương Thanh</strong></p>
              <p className="contact-info">12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.</p>
              <p className="contact-info">📞 Hotline: <strong>0905.999999</strong></p>
              <p className="contact-info">✉️ Email: <strong>phuongthanh@gmail.com</strong></p>
            </div>
          </div>
          <div className="footer-bottom">
            © Copyright 2025. Phương Thanh Express
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Utilities;