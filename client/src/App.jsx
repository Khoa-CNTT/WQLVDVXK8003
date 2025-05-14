import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './contexts/ProtectedRoute';
import Login from './pages/Login';
import Register from './pages/Resgiter';
import Home from './pages/user/Home';
import TicketCrud from './pages/user/Admin/TicketCrud';
import Vehicles from './pages/user/Admin/Vehicles';
import Introductions from './pages/guest/Introductions';
import BookingResults from './pages/guest/BookingResults';
import Ticket_Detail from './pages/guest/Ticket_detail';
import MyBooking from './pages/guest/MyBooking';
import Utilities from './pages/guest/Utilities';
import Security from './pages/guest/Security';
import Profile from './pages/user/Profile';
import MyBookingLogin from './pages/user/MyBookingLogin';
import BookingResultsLogin from './pages/user/BookingResultsLogin';
import Ticket_DetailLogin from './pages/user/Ticket_DetailLogin';
import UtilitiesLogin from './pages/user/UtilitiesLogin';
import SecurityLogin from './pages/user/SecurityLogin';
import Dashboard from './pages/user/Admin/Dashboard';

const App = () => {
  return (
    <Router>
      <AuthProvider>
        <Routes>
          {/* Routes cho người chưa đăng nhập */}
          <Route path="/" element={<Introductions />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route path="/booking-results" element={<BookingResults />} />
          <Route path="/ticket-detail" element={<Ticket_Detail />} />
          <Route path="/my-bookings" element={<MyBooking />} />
          <Route path="/utilities" element={<Utilities />} />
          <Route path="/security" element={<Security />} />

          {/* Routes cho người đã đăng nhập - cần được bảo vệ */}
          <Route
            path="/home"
            element={
              <ProtectedRoute requiredRole={2}>
                <Home />
              </ProtectedRoute>
            }
          />
          <Route
            path="/profile"
            element={
              <ProtectedRoute requiredRole={2}>
                <Profile />
              </ProtectedRoute>
            }
          />
          <Route
            path="/my-bookinglogin"
            element={
              <ProtectedRoute requiredRole={2}>
                <MyBookingLogin />
              </ProtectedRoute>
            }
          />
          <Route
            path="/booking-resultslogin"
            element={
              <ProtectedRoute requiredRole={2}>
                <BookingResultsLogin />
              </ProtectedRoute>
            }
          />
          <Route
            path="/ticket-detaillogin"
            element={
              <ProtectedRoute requiredRole={2}>
                <Ticket_DetailLogin />
              </ProtectedRoute>
            }
          />
          <Route
            path="/securityLogin"
            element={
              <ProtectedRoute requiredRole={2}>
                <SecurityLogin />
              </ProtectedRoute>
            }
          />
          <Route
            path="/utilitiesLogin"
            element={
              <ProtectedRoute requiredRole={2}>
                <UtilitiesLogin />
              </ProtectedRoute>
            }
          />
          <Route
            path="/dashboard"
            element={
              <ProtectedRoute requiredRole={1}>
                <Dashboard />
              </ProtectedRoute>
            }
          />
          <Route
            path="/ticketCrud"
            element={
              <ProtectedRoute requiredRole={1}>
                <TicketCrud />
              </ProtectedRoute>
            }
          />
          <Route
            path="/vehicles"
            element={
              <ProtectedRoute requiredRole={1}>
                <Vehicles />
              </ProtectedRoute>
            }
          />

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