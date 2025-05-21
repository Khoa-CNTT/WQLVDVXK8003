import Dashboard from '../pages/user/Admin/dashboard/Dashboard';
import TicketCrud from '../pages/user/Admin/ticketsCrud/TicketCrud';
import Vehicles from '../pages/user/Admin/vehicles/Vehicles';
import { ProtectedRoute } from '../contexts/ProtectedRoute';
import Customers from '../pages/user/Admin/customers/Customers';
import Amenities from '../pages/user/Admin/amenities/Amenities';
import Drivers from '../pages/user/Admin/drivers/Drivers';
import Line from '../pages/user/Admin/line/Line';
import Trips from '../pages/user/Admin/trips/Trips';


const adminRoutes = [
  {
    path: '/admin',
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
