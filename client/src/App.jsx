import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './contexts/ProtectedRoute';
import publicRoutes from './routes/publicRoutes';
import userRoutes from './routes/userRoutes';
import adminRoutes from './routes/adminRoutes';
import Dashboard from './pages/user/Admin/Dashboard';
import Home from './pages/user/Home';

const App = () => {
  return (
    <Router>
      <AuthProvider>
        <Routes>
          {/* Public routes */}
          {publicRoutes.map(({ path, element }, idx) => (
            <Route key={idx} path={path} element={element} />
          ))}

          {/* User routes */}
          {userRoutes.map(({ path, element }, idx) => (
            <Route key={idx} path={path} element={element} />
          ))}

          {/* Admin routes */}
          {adminRoutes.map(({ path, element }, idx) => (
            <Route key={idx} path={path} element={element} />
          ))}

          {/* Fallback route */}
          <Route
            path="*"
            element={
              <ProtectedRoute>
                {({ user }) => (user?.role_id === 1 ? <Dashboard /> : <Home />)}
              </ProtectedRoute>
            }
          />
        </Routes>
      </AuthProvider>
    </Router>
  );
};

export default App;
