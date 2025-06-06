import React from 'react';
import { Link, NavLink } from 'react-router-dom';

const menuItems = [
  { path: '/admin/', label: 'Thống kê' },
  { path: '/admin/ticketCrud', label: 'Vé xe' },
  { path: '/admin/vehicles', label: 'Phương tiện' },
  { path: '/admin/line', label: 'Tuyến đường' },
  { path: '/admin/trips', label: 'Chuyến xe' },
  { path: '/admin/drivers', label: 'Tài xế' },
  { path: '/admin/customers', label: 'Khách hàng' },
];

const MenuItem = ({ to, children }) => (
  <NavLink
    to={to}
    end={to === '/admin/'} // chỉ dùng end với đường dẫn chính xác (ở đây là /admin/)
    className={({ isActive }) =>
      `menu-item block py-2 px-4 rounded hover:bg-gray-100 ${
        isActive ? 'bg-[#f97316]' : ''
      }`
    }
    style={({ isActive }) => ({
      color: isActive ? 'white' : undefined,
    })}
  >
    {children}
  </NavLink>
);



const Sidebar = () => {
  return (
    <aside className="sidebar w-64 bg-white shadow h-screen p-4">
      <div className="company-name mb-6">
          <Link to="/admin" className="text-2xl font-bold text-orange-500">
          Phương Thanh Express
        </Link>
      </div>
      <nav className="menu flex flex-col gap-2">
        {menuItems.map((item) => (
          <MenuItem key={item.path} to={item.path}>
            {item.label}
          </MenuItem>
        ))}
      </nav>
    </aside>
  );
};

export default Sidebar;
