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
      <ProtectedRoute >
        <Home />
      </ProtectedRoute>
    ),
  },
  {
    path: '/profile',
    element: (
      <ProtectedRoute >
        <Profile />
      </ProtectedRoute>
    ),
  },
  {
    path: '/my-bookinglogin',
    element: (
      <ProtectedRoute >
        <MyBookingLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/booking-resultslogin',
    element: (
      <ProtectedRoute >
        <BookingResultsLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/ticket-detaillogin/:id',
    element: (
      <ProtectedRoute >
        <Ticket_DetailLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/securityLogin',
    element: (
      <ProtectedRoute >
        <SecurityLogin />
      </ProtectedRoute>
    ),
  },
  {
    path: '/utilitiesLogin',
    element: (
      <ProtectedRoute >
        <UtilitiesLogin />
      </ProtectedRoute>
    ),
  },
];

export default userRoutes;
