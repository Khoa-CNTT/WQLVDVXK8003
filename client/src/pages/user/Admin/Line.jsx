import React, { useState } from 'react';
import Table from '../../../components/ReusableTable/Table';
import HomeAdminLayout from '../../../layouts/AdminLayout';
import ReusableModal from '../../../components/ReusableModal/ReusableModal'; // Đường dẫn modal của bạn

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

  const [showModal, setShowModal] = useState(false);
  const [editingLine, setEditingLine] = useState(null);
  const [newLine, setNewLine] = useState({
    name: '',
    seats: '',
    departureDate: '',
    price: '',
  });

  const handleEdit = (line) => {
    setEditingLine(line);
    setNewLine({
      name: line.name,
      seats: line.seats,
      departureDate: line.departureDate,
      price: line.price,
    });
    setShowModal(true);
  };

  const handleDelete = (id) => {
    if (window.confirm(`Bạn chắc chắn muốn xóa tuyến đường ID ${id}?`)) {
      setLines(lines.filter((line) => line.id !== id));
    }
  };

  const handleViewHistory = (id) => {
    alert(`Xem lịch sử đặt vé của tuyến ID: ${id}`);
  };

  const handleSaveLine = () => {
    if (editingLine) {
      // Cập nhật tuyến đường
      setLines(lines.map(line =>
        line.id === editingLine.id
          ? {
              ...editingLine,
              ...newLine,
              seats: parseInt(newLine.seats),
              price: parseInt(newLine.price),
            }
          : line
      ));
    } else {
      // Thêm tuyến đường mới
      const newId = lines.length ? Math.max(...lines.map((l) => l.id)) + 1 : 1;
      setLines([
        ...lines,
        {
          id: newId,
          ...newLine,
          seats: parseInt(newLine.seats),
          price: parseInt(newLine.price),
        },
      ]);
    }

    // Reset form và đóng modal
    setNewLine({ name: '', seats: '', departureDate: '', price: '' });
    setEditingLine(null);
    setShowModal(false);
  };

  const columns = [
    { label: 'ID', key: 'id' },
    { label: 'Tuyến đường', key: 'name' },
    { label: 'Số ghế', key: 'seats' },
    { label: 'Ngày đi', key: 'departureDate' },
    { label: 'Đơn giá', key: 'price' },
    { label: 'Hành động', key: 'actions' },
  ];

  return (
    <HomeAdminLayout>
      <div className="ticket-container">
        <h1 className="page-title">Danh Sách Tuyến Đường</h1>
        <div className="action-bar">
          <button
            className="add-btn"
            onClick={() => {
              setEditingLine(null);
              setNewLine({ name: '', seats: '', departureDate: '', price: '' });
              setShowModal(true);
            }}
          >
            Thêm Tuyến Đường
          </button>
        </div>

        <Table
          columns={columns}
          data={lines.map((line) => ({
            ...line,
            price: line.price.toLocaleString('vi-VN') + ' VNĐ',
            actions: (
              <div className="action-buttons">
                <button className="add-btn" onClick={() => handleViewHistory(line.id)}>Lịch sử đặt vé</button>
                <button className="edit-btn" onClick={() => handleEdit(line)}>Sửa</button>
                <button className="delete-btn" onClick={() => handleDelete(line.id)}>Xóa</button>
              </div>
            ),
          }))}
        />

        <ReusableModal
          title={editingLine ? 'Sửa Tuyến Đường' : 'Thêm Tuyến Đường Mới'}
          show={showModal}
          onClose={() => {
            setShowModal(false);
            setEditingLine(null);
            setNewLine({ name: '', seats: '', departureDate: '', price: '' });
          }}
          onSubmit={handleSaveLine}
        >
          <div className="form-group">
            <label>Tên tuyến:</label>
            <input
              type="text"
              value={newLine.name}
              onChange={(e) => setNewLine({ ...newLine, name: e.target.value })}
              placeholder="VD: Sài Gòn - Huế"
            />
          </div>
          <div className="form-group">
            <label>Số ghế:</label>
            <input
              type="number"
              value={newLine.seats}
              onChange={(e) => setNewLine({ ...newLine, seats: e.target.value })}
              placeholder="VD: 40"
            />
          </div>
          <div className="form-group">
            <label>Ngày đi:</label>
            <input
              type="date"
              value={newLine.departureDate}
              onChange={(e) => setNewLine({ ...newLine, departureDate: e.target.value })}
            />
          </div>
          <div className="form-group">
            <label>Giá vé (VNĐ):</label>
            <input
              type="number"
              value={newLine.price}
              onChange={(e) => setNewLine({ ...newLine, price: e.target.value })}
              placeholder="VD: 300000"
            />
          </div>
        </ReusableModal>
      </div>
    </HomeAdminLayout>
  );
};

export default Line;
