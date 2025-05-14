import React, { useState } from 'react';
import Table from '../../../components/ReusableTable/Table';
import HomeAdminLayout from '../../../layouts/AdminLayout';

const Line = () => {
  const [lines, setLines] = useState([
    {
      id: 1,
      name: 'Sài Gòn - Nha Trang',
      seats: 40,
      departureDate: '2025-06-01',
      price: 250000,
    },
    {
      id: 2,
      name: 'Hà Nội - Đà Nẵng',
      seats: 38,
      departureDate: '2025-06-05',
      price: 320000,
    },
  ]);

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Tuyến đường', key: 'name' },
    { label: 'Số ghế', key: 'seats' },
    { label: 'Ngày đi', key: 'departureDate' },
    { label: 'Đơn giá', key: 'price' },
    { label: 'Hành động', key: 'actions' },
  ];

  const handleEdit = (id) => {
    alert(`Sửa tuyến đường có ID: ${id}`);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Bạn chắc chắn muốn xóa tuyến đường ID ${id}?`)) {
      setLines(lines.filter(line => line.id !== id));
    }
  };

  const handleViewHistory = (id) => {
    alert(`Xem lịch sử đặt vé của tuyến ID: ${id}`);
  };

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Tuyến Đường</h1>
        <div className="action-bar">
          <button className="add-btn">Thêm Tuyến Đường</button>
        </div>

        <Table
          columns={columns}
          data={lines.map(line => ({
            ...line,
            price: line.price.toLocaleString('vi-VN') + ' VNĐ',
            actions: (
              <div className="action-buttons">
                <button className="add-btn" onClick={() => handleViewHistory(line.id)}>Lịch sử đặt vé</button>
                <button className="edit-btn" onClick={() => handleEdit(line.id)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDelete(line.id)}>Xóa</button>
              </div>
            ),
          }))}
        />
      </div>
    </HomeAdminLayout>
  );
};

export default Line;
