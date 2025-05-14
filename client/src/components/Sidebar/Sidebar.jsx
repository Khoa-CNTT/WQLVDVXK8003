import React from 'react';
import { NavLink } from 'react-router-dom';

const menuItems = [
  { path: '/dashboard', label: 'Thống kê' },
  { path: '/ticketCrud', label: 'Vé xe' },
  { path: '/vehicles', label: 'Phương tiện' },
  { path: '/line', label: 'Tuyến đường' },
  { path: '/trips', label: 'Chuyến xe' },
  { path: '/drivers', label: 'Tài xế' },
  { path: '/customers', label: 'Khách hàng' },
  { path: '/amenities', label: 'Tiện ích' },
];

const MenuItem = ({ to, children }) => (
  <NavLink
    to={to}
    className={({ isActive }) =>
      `menu-item block py-2 px-4 rounded hover:bg-gray-100 ${
        isActive ? 'text-[#f97316] font-semibold' : 'text-gray-700'
      }`
    }
  >
    {children}
  </NavLink>
);

const Sidebar = () => {
  return (
    <aside className="sidebar w-64 bg-white shadow h-screen p-4">
      <div className="company-name mb-6">
        <h1 className="text-2xl font-bold text-orange-500">Phương Thanh Express</h1>
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
