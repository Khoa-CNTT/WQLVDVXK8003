import Dashboard from '../pages/user/Admin/Dashboard';
import TicketCrud from '../pages/user/Admin/TicketCrud';
import Vehicles from '../pages/user/Admin/Vehicles';
import { Trips } from '../pages/user/Admin/Trips';
import { ProtectedRoute } from '../contexts/ProtectedRoute';
import Line from '../pages/user/Admin/line';

const adminRoutes = [
  {
    path: '/dashboard',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Dashboard />
      </ProtectedRoute>
    ),
  },
  {
    path: '/ticketCrud',
    element: (
      <ProtectedRoute requiredRole={1}>
        <TicketCrud />
      </ProtectedRoute>
    ),
  },
  {
    path: '/vehicles',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Vehicles />
      </ProtectedRoute>
    ),
  },
  {
    path: '/trips',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Trips />
      </ProtectedRoute>
    ),
  },
    {
    path: '/line',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Line />
      </ProtectedRoute>
    ),
  },
];

export default adminRoutes;
