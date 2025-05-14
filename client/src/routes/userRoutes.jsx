import Home from '../pages/user/Home';
import Profile from '../pages/user/Profile';
import MyBookingLogin from '../pages/user/MyBookingLogin';
import BookingResultsLogin from '../pages/user/BookingResultsLogin';
import Ticket_DetailLogin from '../pages/user/Ticket_DetailLogin';
import UtilitiesLogin from '../pages/user/UtilitiesLogin';
import SecurityLogin from '../pages/user/SecurityLogin';
import { ProtectedRoute } from '../contexts/ProtectedRoute';

const userRoutes = [
  {
    path: '/home',
    element: (
      <ProtectedRoute requiredRole={2}>
        <Home />
      </ProtectedRoute>
    ),
  },
  {
    path: '/profile',
    element: (
      <ProtectedRoute requiredRole={2}>
        <Profile />
      </ProtectedRoute>
    ),
  },
  {
    path: '/my-bookinglogin',
    element: (
      <ProtectedRoute requiredRole={2}>
        <MyBookingLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/booking-resultslogin',
    element: (
      <ProtectedRoute requiredRole={2}>
        <BookingResultsLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/ticket-detaillogin',
    element: (
      <ProtectedRoute requiredRole={2}>
        <Ticket_DetailLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/securityLogin',
    element: (
      <ProtectedRoute requiredRole={2}>
        <SecurityLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/utilitiesLogin',
    element: (
      <ProtectedRoute requiredRole={2}>
        <UtilitiesLogin />
      </ProtectedRoute>
    ),
  },
];

export default userRoutes;
