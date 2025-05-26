import Swal from 'sweetalert2';
import Sidebar from '../components/Sidebar/Sidebar';
import { Storage } from '../constant/storage';

const HomeAdminLayout = ({ children }) => {
  const currentDateTime = new Date().toLocaleString();
  // Hàm đăng xuất
  const handleLogout = () => {
    Swal.fire({
      title: 'Đăng xuất?',
      text: 'Bạn có chắc muốn đăng xuất?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Đăng xuất',
      cancelButtonText: 'Hủy',
    }).then((result) => {
      if (result.isConfirmed) {
        localStorage.removeItem(Storage.AUTH_DATA);
        localStorage.removeItem('userInfo');
        window.location.href = '/';
      }
    });
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
