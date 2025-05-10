import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ProtectedRoute } from './contexts/ProtectedRoute';
import Login from './views/Login';
import Register from './views/Resgiter';
import Home from './views/phailogin/Home';
import Admin from './views/phailogin/Admin/Dashboard';
import TicketCrud from './views/phailogin/Admin/TicketCrud';
import Vehicles from './views/phailogin/Admin/Vehicles';
import Introductions from './views/kcanlogin/Introductions';
import BookingResults from './views/kcanlogin/BookingResults';
import Ticket_Detail from './views/kcanlogin/Ticket_detail';
import MyBooking from './views/kcanlogin/MyBooking';
import Utilities from './views/kcanlogin/Utilities';
import Security from './views/kcanlogin/Security';
import Profile from './views/phailogin/Profile';
import MyBookingLogin from './views/phailogin/MyBookingLogin';
import BookingResultsLogin from './views/phailogin/BookingResultsLogin';
import Ticket_DetailLogin from './views/phailogin/Ticket_DetailLogin';
import UtilitiesLogin from './views/phailogin/UtilitiesLogin';
import SecurityLogin from './views/phailogin/SecurityLogin';
import Dashboard from './views/phailogin/Admin/Dashboard';

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