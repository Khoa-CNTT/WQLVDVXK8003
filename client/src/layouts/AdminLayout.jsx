import React from 'react';
import Sidebar from '../components/Sidebar/Sidebar';

const HomeAdminLayout = ({ children }) => {
  const currentDateTime = new Date().toLocaleString(); 
  // Hàm đăng xuất
    const handleLogout = () => {
        if (window.confirm('Bạn có chắc muốn đăng xuất?')) {
            localStorage.removeItem('authData');
            localStorage.removeItem('userInfo');
            window.location.href = '/login';
        }
    };


  return (
    <div className="app-container min-w-screen">
      <Sidebar />
      <main className="main-content">
        {/* Header */}
        <header className="top-header">
          <div className="date-time">{currentDateTime}</div>
          <div className="user-section">
            <span className="user-email">admin@phuongthanh.com</span>
            <button className="logout-btn" onClick={handleLogout}>Đăng Xuất</button>
          </div>
        </header>
        {/* Nội dung trang thay đổi */}
        {children} 
      </main>
    </div>
  );
};

export default HomeAdminLayout;
