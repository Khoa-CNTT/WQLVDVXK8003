import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate } from 'react-router-dom';

const Register = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: ''
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);
    
    console.log('Dữ liệu đăng ký gửi đi:', formData);
    
    if (formData.password !== formData.password_confirmation) {
      setError('Mật khẩu xác nhận không khớp');
      setLoading(false);
      return;
    }
    
    try {
      const result = await register(formData);
      console.log('Kết quả đăng ký:', result);
      
      if (result.success) {
        setSuccess('Đăng ký thành công! Đang chuyển hướng đến trang đăng nhập...');
        setTimeout(() => {
          navigate('/login');
        }, 2000);
      } else {
        // Xử lý thông báo lỗi chi tiết
        if (result.error && result.error.errors) {
          // Nếu có lỗi xác thực cụ thể từ Laravel
          const errorMessages = Object.values(result.error.errors).flat();
          setError(errorMessages.join(', '));
        } else {
          setError(result.message || 'Đăng ký không thành công. Vui lòng thử lại.');
        }
      }
    } catch (err) {
      console.error('Lỗi trong quá trình đăng ký:', err);
      setError('Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại sau.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="register-container">
      <h2>Đăng ký</h2>
      
      {error && <div className="error-message">{error}</div>}
      {success && <div className="success-message">{success}</div>}
      
      <form onSubmit={handleSubmit}>
        <div>
          <label>Tên:</label>
          <input
            type="text"
            name="name"
            value={formData.name}
            onChange={handleChange}
            required
          />
        </div>
        <div>
          <label>Email:</label>
          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
          />
        </div>
        <div>
          <label>Điện thoại:</label>
          <input
            type="tel"
            name="phone"
            value={formData.phone}
            onChange={handleChange}
            required
          />
        </div>
        <div>
          <label>Mật khẩu:</label>
          <input
            type="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            required
            minLength="8"
          />
        </div>
        <div>
          <label>Xác nhận mật khẩu:</label>
          <input
            type="password"
            name="password_confirmation"
            value={formData.password_confirmation}
            onChange={handleChange}
            required
            minLength="8"
          />
        </div>
        <button 
          type="submit" 
          disabled={loading}
        >
          {loading ? 'Đang xử lý...' : 'Đăng ký'}
        </button>
      </form>
      
      <p>
        Bạn đã có tài khoản? <button onClick={() => navigate('/login')}>Đăng nhập</button>
      </p>
    </div>
  );
};

export default Register;