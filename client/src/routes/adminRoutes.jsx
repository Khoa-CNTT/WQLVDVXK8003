import Dashboard from '../pages/user/Admin/Dashboard';
import TicketCrud from '../pages/user/Admin/TicketCrud';
import Vehicles from '../pages/user/Admin/Vehicles';
import { Trips } from '../pages/user/Admin/Trips';
import { ProtectedRoute } from '../contexts/ProtectedRoute';
import Line from '../pages/user/Admin/line';
import Customers from '../pages/user/Admin/Customers';
import Amenities from '../pages/user/Admin/Amenities';
import Drivers from '../pages/user/Admin/Drivers';

const adminRoutes = [
  {
    path: '/admin/dashboard',
    element: (
      <ProtectedRoute >
        <Dashboard />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/ticketCrud',
    element: (
      <ProtectedRoute >
        <TicketCrud />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/vehicles',
    element: (
      <ProtectedRoute >
        <Vehicles />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/trips',
    element: (
      <ProtectedRoute >
        <Trips />
      </ProtectedRoute>
    ),
  },
    {
    path: '/admin/line',
    element: (
      <ProtectedRoute >
        <Line />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/customers',
    element: (
      <ProtectedRoute >
        <Customers />
      </ProtectedRoute>
    ),
  },
  {
    path: '/admin/amenities',
    element: (
      <ProtectedRoute >
        <Amenities />
      </ProtectedRoute>
    ),
  },
    {
    path: '/admin/drivers',
    element: (
      <ProtectedRoute >
        <Drivers />
      </ProtectedRoute>
    ),
  },
  
];

export default adminRoutes;
