import Dashboard from '../pages/user/Admin/Dashboard';
import TicketCrud from '../pages/user/Admin/TicketCrud';
import Vehicles from '../pages/user/Admin/Vehicles';
import { Trips } from '../pages/user/Admin/Trips';
import { ProtectedRoute } from '../contexts/ProtectedRoute';
import Line from '../pages/user/Admin/line';
import { Drivers } from '../pages/user/Admin/Drivers';
import Customers from '../pages/user/Admin/Customers';
import Amenities from '../pages/user/Admin/Amenities';

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
  {
    path: '/customers',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Customers />
      </ProtectedRoute>
    ),
  },
  {
    path: '/amenities',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Amenities />
      </ProtectedRoute>
    ),
  },
    {
    path: '/drivers',
    element: (
      <ProtectedRoute requiredRole={1}>
        <Drivers />
      </ProtectedRoute>
    ),
  },
  
];

export default adminRoutes;
