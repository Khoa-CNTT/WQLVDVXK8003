import {
  createContext,
  useContext,
  useState,
  useEffect,
} from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const AuthContext = createContext();

const getInitialAuthData = ()=> {
  const localUserInfo = localStorage.getItem('userInfo')

  if (localUserInfo){
    const parsedUserInfo = JSON.parse(localUserInfo)


    return {data:{user:parsedUserInfo}}
  }
  return null

}

export const AuthProvider = ({ children }) => {
  const [authData, setAuthData] = useState(getInitialAuthData());
  const [loading, setLoading] = useState(true);
  console.log('authData', authData)
  const navigate = useNavigate();

  // Load authData từ localStorage và validate
  useEffect(() => {
    const initializeAuth = async () => {
      try {
        const urlParams = new URLSearchParams(window.location.search);
        const pathname = window.location.pathname;

        // Nếu đang logout
        if (urlParams.get('logout') === 'true' && pathname === '/') {
          logout(false);
          return;
        }

        const stored = localStorage.getItem('authData');
        if (!stored) return;

        const parsed = JSON.parse(stored);
        const token = parsed?.data?.access_token;
        const refreshToken = parsed?.data?.refresh_token;

        if (!token) return;

        try {
          // Gọi API user
          const response = await axios.get('http://127.0.0.1:8000/api/v1/user', {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          });

          const updatedAuth = {
            ...parsed,
            data: {
              ...parsed.data,
              user: response.data.data,
            },
          };

          setAuthData(updatedAuth);
          localStorage.setItem('authData', JSON.stringify(updatedAuth));
        } catch (err) {
          if (err.response?.status === 401 && refreshToken) {
            await refreshAccessToken(parsed);
          } else {
            console.error('Failed to fetch user:', err);
          }
        }
      } catch (err) {
        console.error('Auth init error:', err);
      } finally {
        setLoading(false);
      }
    };

    initializeAuth();
  }, []);

  // Refresh token
  const refreshAccessToken = async (parsedAuth) => {
    try {
      const res = await axios.post('http://127.0.0.1:8000/api/v1/refresh', {
        refresh_token: parsedAuth.data.refresh_token,
      });

      const newAuth = {
        ...parsedAuth,
        data: {
          ...parsedAuth.data,
          access_token: res.data.access_token,
          refresh_token: res.data.refresh_token || parsedAuth.data.refresh_token,
        },
      };

      localStorage.setItem('authData', JSON.stringify(newAuth));
      setAuthData(newAuth);

      // Fetch lại thông tin user
      const userRes = await axios.get('http://127.0.0.1:8000/api/v1/user', {
        headers: {
          Authorization: `Bearer ${newAuth.data.access_token}`,
        },
      });

      setAuthData((prev) => ({
        ...prev,
        data: {
          ...prev.data,
          user: userRes.data.data,
        },
      }));
    } catch (error) {
      console.error('Token refresh failed:', error);
      logout(false);
    }
  };

  // Login
  const login = async (email, password, redirectUrl = '/home') => {
    try {
      if (window.location.search.includes('logout=true')) {
        window.history.replaceState({}, document.title, window.location.pathname);
      }

      const { data: response } = await axios.post('http://127.0.0.1:8000/api/v1/login', {
        email,
        password,
      });

      const { success, message, data } = response;

      if (!success) throw new Error(message || 'Login failed');

      localStorage.setItem('authData', JSON.stringify(response));
      localStorage.setItem('userInfo', JSON.stringify(data.user));
      setAuthData(response);

      // Determine redirect path
      // let targetUrl = '/home';
      // if (data.user.role_id === 1) targetUrl = '/admin/dashboard';
      // else if (redirectUrl.includes('/ticket-detail') && redirectUrl.includes('?')) {
      //   const query = redirectUrl.substring(redirectUrl.indexOf('?'));
      //   targetUrl = '/ticket-detailLogin' + query;
      // } else if (redirectUrl && redirectUrl !== '/home' && redirectUrl !== '/') {
      //   targetUrl = redirectUrl;
      // }

      // navigate(targetUrl, { replace: true });
      return { success: true, data: response };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || error.message,
        error: error.response?.data,
      };
    }
  };

  // Register
  const register = async (userData) => {
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

  // Logout
  const logout = (redirect = true) => {
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
      console.error('Logout failed:', err);
    }

    localStorage.removeItem('authData');
    localStorage.removeItem('userInfo');
    setAuthData(null);

    if (redirect) navigate('/?logout=true', { replace: true });
  };

  // Context value
  const value = {
    authData,
    loading,
    login,
    register,
    logout,
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
