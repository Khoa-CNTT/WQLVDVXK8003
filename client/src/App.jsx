import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './contexts/ProtectedRoute';
import publicRoutes from './routes/publicRoutes';
import userRoutes from './routes/userRoutes';
import adminRoutes from './routes/adminRoutes';
import Dashboard from './pages/user/Admin/dashboard/Dashboard';
import Home from './pages/user/Home';
import PublicRoute from './contexts/PublicRoute';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const App = () => {
  return (
    <>
      <Router>
        <AuthProvider>
          <Routes>
            {/* Public routes */}

            <Route element={<PublicRoute />}>
              {publicRoutes.map(({ path, element }, idx) => (
                <Route key={idx} path={path} element={element} />
              ))}
            </Route>


            {/* User routes */}
            {userRoutes.map(({ path, element }, idx) => (
              <Route
                key={idx}
                path={path}
                element={<ProtectedRoute requiredRole={1}>{element}</ProtectedRoute>}
              />
            ))}

            {/* Admin routes */}
            {adminRoutes.map(({ path, element }, idx) => (
              <Route
                key={idx}
                path={path}
                element={<ProtectedRoute requiredRole={1}>{element}</ProtectedRoute>}
              />
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
      <ToastContainer
        position="top-right"
        autoClose={5000}
        hideProgressBar={false}
        newestOnTop={false}
        closeOnClick
        rtl={false}
        pauseOnFocusLoss
        draggable
        pauseOnHover
        theme="colored"
      />
    </>
  );
};

export default App;
