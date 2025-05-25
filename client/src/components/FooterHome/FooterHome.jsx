import { Link } from 'react-router-dom';

export default function FooterHome() {
    return (
        <>
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
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.1104391547477!2d108.19966061484894!3d16.059718588885864!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3142190fbfdfd6c7%3A0x33bd6048f8e47311!2zxJDDoCBO4bq1bmcsIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1652344895954!5m2!1svi!2s"
                                className="map-iframe"
                                allowFullScreen
                                loading="lazy"
                                referrerPolicy="no-referrer-when-downgrade"
                                title="Map"
                            ></iframe>
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
                            <img
                                src="https://th.bing.com/th?q=Momo+Icon+App+PNG&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247"
                                className="payment-logo"
                                alt="Momo"
                            />
                            <img
                                src="https://th.bing.com/th?q=Vnpay+Logo.png&w=120&h=120&c=1&rs=1&qlt=90&cb=1&dpr=1.3&pid=InlineBlock&mkt=en-WW&cc=VN&setlang=en&adlt=moderate&t=1&mw=247"
                                className="payment-logo"
                                alt="VNPAY"
                            />
                        </div>
                    </div>

                    <div className="footer-column">
                        <h3 className="footer-title">LIÊN HỆ</h3>
                        <p className="contact-info">
                            Công ty TNHH Vận Tải <strong>Phương Thanh</strong>
                        </p>
                        <p className="contact-info">
                            12 Bàu Cầu 12, xã Hòa Xuân, huyện Hòa Vang, Đà Nẵng.
                        </p>
                        <p className="contact-info">📞 Mã số thuế: <strong>1111111</strong></p>
                        <p className="contact-info">📞 Hotline: <strong>0905.999999</strong></p>
                        <p className="contact-info">✉️ Email: <strong>phuongthanh@gmail.com</strong></p>
                    </div>
                </div>

                <div className="copyright">
                    © Copyright 2025. Phương Thanh Express
                </div>
            </footer>
        </>

    );
}
