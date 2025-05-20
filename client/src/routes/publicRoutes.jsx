import Introductions from '../pages/guest/Introductions';
import Login from '../pages/Login';
import Register from '../pages/Resgiter';
import BookingResults from '../pages/guest/BookingResults';
import Ticket_Detail from '../pages/guest/Ticket_detail';
import MyBooking from '../pages/guest/MyBooking';
import Utilities from '../pages/guest/Utilities';
import Security from '../pages/guest/Security';
import ForgotPassword from '../pages/ForgotPassword';

const publicRoutes = [
  { path: '/', element: <Introductions /> },
  { path: '/login', element: <Login /> },
  { path: '/register', element: <Register /> },
  { path: '/booking-results', element: <BookingResults /> },
  { path: '/ticket-detail', element: <Ticket_Detail /> },
  { path: '/my-bookings', element: <MyBooking /> },
  { path: '/utilities', element: <Utilities /> },
  { path: '/security', element: <Security /> },
  { path: '/forgot-password', element: <ForgotPassword /> },
];

export default publicRoutes;
