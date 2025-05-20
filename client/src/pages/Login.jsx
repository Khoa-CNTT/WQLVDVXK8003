import { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useNavigate, useLocation } from 'react-router-dom';
import './Login.css';

const Login = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const { login } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const params = new URLSearchParams(location.search);
  const redirectUrl = params.get('redirect') || '/home';

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');

    console.log('Attempting login with:', email);
    console.log('Redirect URL after login:', redirectUrl);

    const result = await login(email, password, redirectUrl);

    console.log('Login result:', result);
    if (!result.success) {
      setError(result.message || 'Đăng nhập thất bại. Vui lòng thử lại.');
    }
  };

  return (
    <div className="login-container">
      <h2>Đăng Nhập</h2>
      <p>Vui lòng nhập thông tin tài khoản</p>

      {error && <div className="error-message">{error}</div>}

      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            placeholder="Nhập email"
          />
        </div>
        <div>
          <label>Mật khẩu:</label>
          <input
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            placeholder="Nhập mật khẩu"
          />
        </div>
        <button type="submit">Đăng nhập</button>
      </form>

      <div className="forgot-password">
        <button
          type="button"
          className="forgot-password-link"
          onClick={() => navigate('/forgot-password', { replace: true })}
          style={{ background: 'none', border: 'none', color: '#ff6600', cursor: 'pointer', textDecoration: 'underline', padding: 0 }}
        >
          Quên mật khẩu?
        </button>
      </div>

      <div className="register-link">
        <p>
          Chưa có tài khoản?{' '}
          <button onClick={() => navigate('/register', { replace: true })}>Đăng ký</button>
        </p>
      </div>

      <div className="back-to-home">
        <a href="/">← Quay lại trang chủ</a>
      </div>
    </div>
  );
};

export default Login;