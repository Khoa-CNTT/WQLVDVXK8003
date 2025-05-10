import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../../contexts/AuthContext';
import './Profile.css';

const Profile = () => {
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  
  // States
  const [loading, setLoading] = useState(true);
  const [userProfile, setUserProfile] = useState(null);
  const [isEditing, setIsEditing] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: ''
  });
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    password: '',
    password_confirmation: ''
  });
  const [stats, setStats] = useState({
    booking_count: 0,
    ticket_count: 0
  });

  // Format date
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('vi-VN', {
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

  // Handle input change for profile form
  const handleProfileInputChange = (e) => {
    const { id, value } = e.target;
    setFormData({
      ...formData,
      [id]: value
    });
  };

  // Handle input change for password form
  const handlePasswordInputChange = (e) => {
    const { id, value } = e.target;
    setPasswordData({
      ...passwordData,
      [id]: value
    });
  };

  // Load user profile
  useEffect(() => {
    const loadProfile = async () => {
      // Check if user is logged in
      if (!isAuthenticated) {
        setLoading(false);
        return;
      }

      try {
        // In a real app, you would fetch the user profile from your API
        // const response = await api.getUserProfile();
        // const profileData = response.data;
        
        // For now, use dummy data or user info from AuthContext
        const dummyProfile = {
          name: user?.name || "Nguyễn Văn A",
          email: user?.email || "nguyen.van.a@example.com",
          phone: user?.phone || "0123456789",
          created_at: user?.created_at || "2023-01-01T00:00:00Z"
        };
        
        const dummyStats = {
          booking_count: 5,
          ticket_count: 8
        };
        
        // Update states
        setUserProfile(dummyProfile);
        setFormData({
          name: dummyProfile.name,
          email: dummyProfile.email,
          phone: dummyProfile.phone
        });
        setStats(dummyStats);
        setLoading(false);
        
      } catch (error) {
        console.error("Error loading profile:", error);
        
        // Use dummy data for testing
        const dummyProfile = {
          name: "Nguyễn Văn A",
          email: "nguyen.van.a@example.com",
          phone: "0123456789",
          created_at: "2023-01-01T00:00:00Z"
        };
        
        const dummyStats = {
          booking_count: 5,
          ticket_count: 8
        };
        
        setUserProfile(dummyProfile);
        setFormData({
          name: dummyProfile.name,
          email: dummyProfile.email,
          phone: dummyProfile.phone
        });
        setStats(dummyStats);
        setLoading(false);
      }
    };

    loadProfile();
  }, [isAuthenticated, user]);

  // Handle profile update
  const handleProfileUpdate = async (e) => {
    e.preventDefault();
    
    try {
      // In a real app, call the API to update profile
      // await api.updateProfile(formData);
      
      // Update local user data
      setUserProfile({
        ...userProfile,
        name: formData.name,
        email: formData.email,
        phone: formData.phone
      });
      
      // Show success message
      showNotification("Cập nhật thông tin thành công", "success");
      
      // Switch back to view mode
      setIsEditing(false);
      
    } catch (error) {
      showNotification(`Lỗi cập nhật thông tin: ${error.message}`, "error");
    }
  };

  // Handle password update
  const handlePasswordUpdate = async (e) => {
    e.preventDefault();
    
    if (passwordData.password !== passwordData.password_confirmation) {
      showNotification("Mật khẩu xác nhận không khớp", "error");
      return;
    }
    
    try {
      // In a real app, call the API to update password
      // await api.updatePassword(passwordData);
      
      // Show success message
      showNotification("Cập nhật mật khẩu thành công", "success");
      
      // Clear form
      setPasswordData({
        current_password: '',
        password: '',
        password_confirmation: ''
      });
      
    } catch (error) {
      showNotification(`Lỗi cập nhật mật khẩu: ${error.message}`, "error");
    }
  };

  return (
    <div className="profile-page">
      {/* Header */}
      <header>
        <div className="container">
          <h1>Phương Thanh Express</h1>
          <Link to="/home" className="back-link">Quay lại Trang Chủ</Link>
        </div>
      </header>
      
      <section className="container">
        <div className="profile-container">
          <h2>THÔNG TIN CÁ NHÂN</h2>
          
          {loading ? (
            <div className="loading">
              <p>Đang tải thông tin cá nhân...</p>
            </div>
          ) : !isAuthenticated ? (
            <div className="login-required">
              <p>Vui lòng đăng nhập để xem thông tin cá nhân.</p>
              <div className="auth-buttons">
                <Link to="/login" className="btn-modern">Đăng nhập</Link>
                <Link to="/register" className="btn-secondary">Đăng ký</Link>
              </div>
            </div>
          ) : (
            <>
              {!isEditing ? (
                <div className="profile-content">
                  <div className="profile-section">
                    <h3>Thông tin cơ bản</h3>
                    
                    <div className="profile-info">
                      <div className="info-item">
                        <p className="info-label">Họ và tên:</p>
                        <p className="info-value">{userProfile?.name}</p>
                      </div>
                      <div className="info-item">
                        <p className="info-label">Email:</p>
                        <p className="info-value">{userProfile?.email}</p>
                      </div>
                      <div className="info-item">
                        <p className="info-label">Số điện thoại:</p>
                        <p className="info-value">{userProfile?.phone}</p>
                      </div>
                      <div className="info-item">
                        <p className="info-label">Ngày tham gia:</p>
                        <p className="info-value">{formatDate(userProfile?.created_at)}</p>
                      </div>
                    </div>
                    
                    <div className="action-buttons">
                      <button onClick={() => setIsEditing(true)} className="btn-modern">
                        Chỉnh sửa thông tin
                      </button>
                    </div>
                  </div>
                  
                  <div className="profile-section">
                    <h3>Đổi mật khẩu</h3>
                    
                    <form onSubmit={handlePasswordUpdate}>
                      <div className="form-group">
                        <label htmlFor="current_password">Mật khẩu hiện tại:</label>
                        <input 
                          type="password" 
                          id="current_password" 
                          value={passwordData.current_password}
                          onChange={handlePasswordInputChange}
                          required 
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="password">Mật khẩu mới:</label>
                        <input 
                          type="password" 
                          id="password" 
                          value={passwordData.password}
                          onChange={handlePasswordInputChange}
                          required 
                          minLength="8" 
                        />
                      </div>
                      
                      <div className="form-group">
                        <label htmlFor="password_confirmation">Xác nhận mật khẩu mới:</label>
                        <input 
                          type="password" 
                          id="password_confirmation" 
                          value={passwordData.password_confirmation}
                          onChange={handlePasswordInputChange}
                          required 
                          minLength="8" 
                        />
                      </div>
                      
                      <div className="action-buttons">
                        <button type="submit" className="btn-modern">Cập nhật mật khẩu</button>
                      </div>
                    </form>
                  </div>
                  
                  <div className="profile-section">
                    <h3>Thống kê</h3>
                    
                    <div className="stats-container">
                      <div className="stat-item">
                        <p className="stat-label">Tổng số lần đặt vé:</p>
                        <p className="stat-value">{stats.booking_count}</p>
                      </div>
                      <div className="stat-item">
                        <p className="stat-label">Tổng số vé đã đặt:</p>
                        <p className="stat-value">{stats.ticket_count}</p>
                      </div>
                    </div>
                    
                    <div className="action-buttons center">
                      <Link to="/my-bookings" className="btn-modern">Xem vé đã đặt</Link>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="edit-profile-form">
                  <h3>Chỉnh sửa thông tin cá nhân</h3>
                  
                  <form onSubmit={handleProfileUpdate}>
                    <div className="form-group">
                      <label htmlFor="name">Họ và tên:</label>
                      <input 
                        type="text" 
                        id="name" 
                        value={formData.name}
                        onChange={handleProfileInputChange}
                        required 
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="email">Email:</label>
                      <input 
                        type="email" 
                        id="email" 
                        value={formData.email}
                        onChange={handleProfileInputChange}
                        required 
                      />
                    </div>
                    
                    <div className="form-group">
                      <label htmlFor="phone">Số điện thoại:</label>
                      <input 
                        type="tel" 
                        id="phone" 
                        value={formData.phone}
                        onChange={handleProfileInputChange}
                        required 
                      />
                    </div>
                    
                    <div className="action-buttons">
                      <button 
                        type="button" 
                        onClick={() => setIsEditing(false)} 
                        className="btn-secondary"
                      >
                        Hủy
                      </button>
                      <button type="submit" className="btn-modern">Lưu thay đổi</button>
                    </div>
                  </form>
                </div>
              )}
            </>
          )}
        </div>
      </section>
    </div>
  );
};

export default Profile;