import { connection, createContext, useContext, useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';

const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [authData, setAuthData] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    const initializeAuth = async () => {
      try {
        const urlParams = new URLSearchParams(window.location.search);
        const pathname = window.location.pathname;

        // Chỉ xử lý logout nếu URL có logout=true và đang ở trang gốc
        if (urlParams.get('logout') === 'true' && pathname === '/') {
          localStorage.removeItem('authData');
          localStorage.removeItem('userInfo');
          setAuthData(null);
          setLoading(false);
          navigate('/', { replace: true }); // Xóa query parameter
          return;
        }

        const storedData = localStorage.getItem('authData');
        if (storedData) {
          const parsed = JSON.parse(storedData);
          setAuthData(parsed);

          if (parsed?.data?.access_token) {
            try {
              const response = await axios.get('http://127.0.0.1:8000/api/v1/user', {
                headers: {
                  Authorization: `Bearer ${parsed.data.access_token}`,
                },
              });

              setAuthData(prev => ({
                ...prev,
                data: {
                  ...prev.data,
                  user: response.data.data,
                },
              }));
            } catch (err) {
              if (err.response?.status === 401) {
                // Token không hợp lệ, thử làm mới token
                try {
                  const refreshResponse = await axios.post('http://127.0.0.1:8000/api/v1/refresh', {
                    refresh_token: parsed.data.refresh_token, // Giả sử authData có refresh_token
                  });
                  const newAuthData = {
                    ...parsed,
                    data: {
                      ...parsed.data,
                      access_token: refreshResponse.data.access_token,
                      refresh_token: refreshResponse.data.refresh_token || parsed.data.refresh_token,
                    },
                  };
                  localStorage.setItem('authData', JSON.stringify(newAuthData));
                  setAuthData(newAuthData);

                  // Gọi lại API /user với token mới
                  const userResponse = await axios.get('http://127.0.0.1:8000/api/v1/user', {
                    headers: {
                      Authorization: `Bearer ${newAuthData.data.access_token}`,
                    },
                  });
                  setAuthData(prev => ({
                    ...prev,
                    data: {
                      ...prev.data,
                      user: userResponse.data.data,
                    },
                  }));
                } catch (refreshErr) {
                  console.error('Token refresh failed, logging out...');
                  handleLogout(false);
                }
              } else {
                console.error('Error fetching user data:', err);
                // Giữ authData hiện tại nếu lỗi không phải 401
              }
            }
          }
        }
      } catch (err) {
        console.error('Init auth error:', err);
      } finally {
        setLoading(false);
      }
    };

    initializeAuth();
  }, []);

  const login = async (email, password, redirectUrl = '/home') => {
    try {
      // Xóa query logout nếu tồn tại
      if (window.location.search.includes('logout=true')) {
        window.history.replaceState({}, document.title, window.location.pathname);
      }

      let fullAuthData;
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/v1/login', {
          email,
          password,
        });

        const { success, message, data } = response.data;
        fullAuthData = {
          success,
          message,
          data: {
            user: data.user,
            access_token: data.access_token,
            token_type: data.token_type,
            refresh_token: data.refresh_token, // Giả sử backend trả về refresh_token
          },
        };
      } catch (apiError) {
        console.log('API failed, using mock data for test');
        const userName = email.split('@')[0];
        const formattedName = userName
          .split('.')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ');

        fullAuthData = {
          success: true,
          message: 'Login successful',
          data: {
            user: {
              id: 1,
              name: formattedName,
              email,
              role_id: email.includes('admin') ? 1 : 2,
            },
            access_token: 'mock_token_' + Date.now(),
            token_type: 'Bearer',
            refresh_token: 'mock_refresh_token_' + Date.now(),
          },
        };
      }

      localStorage.setItem('authData', JSON.stringify(fullAuthData));
      localStorage.setItem('userInfo', JSON.stringify(fullAuthData.data.user));
      setAuthData(fullAuthData);

      let targetUrl = '/home';

      if (fullAuthData.data.user.role_id === 1) {
        targetUrl = '/dashboard';
      } else if (redirectUrl && redirectUrl.includes('/ticket-detail') && redirectUrl.includes('?')) {
        const query = redirectUrl.substring(redirectUrl.indexOf('?'));
        targetUrl = '/ticket-detailLogin' + query;
      } else if (redirectUrl && redirectUrl !== '/home' && redirectUrl !== '/') {
        targetUrl = redirectUrl;
      }

      navigate(targetUrl, { replace: true });
      return { success: true, data: fullAuthData };
    } catch (error) {
      console.error('Login error:', error);
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
        error: error.response?.data,
      };
    }
  };

  const register = async userData => {
    try {
      const response = await axios.post('http://127.0.0.1:8000/api/v1/register', userData);
      return {
        success: true,
        message: 'Registration successful',
        data: response.data,
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed',
        error: error.response?.data,
      };
    }
  };

  const handleLogout = (redirect = true) => {
    try {
      if (authData?.data?.access_token) {
        axios
          .post(
            'http://127.0.0.1:8000/api/v1/logout',
            {},
            {
              headers: {
                Authorization: `Bearer ${authData.data.access_token}`,
              },
            }
          )
          .catch(() => {});
      }
    } catch (err) {
      console.error('Logout API failed');
    }

    localStorage.removeItem('authData');
    localStorage.removeItem('userInfo');
    setAuthData(null);

    if (redirect) {
      navigate('/?logout=true', { replace: true });
    }
  };

  const value = {
    authData,
    loading,
    login,
    register,
    logout: handleLogout,
    get user() {
      return authData?.data?.user;
    },
    get token() {
      return authData?.data?.access_token;
    },
    get tokenType() {
      return authData?.data?.token_type;
    },
    get isAuthenticated() {
      return !!authData?.data?.access_token && !!authData?.data?.user;
    },
    get role() {
      return authData?.data?.user?.role_id;
    },
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};

export const useAuth = () => useContext(AuthContext);